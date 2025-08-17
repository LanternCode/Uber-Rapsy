<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!isset($_SESSION))
    session_start();

/**
 * The controller responsible for handling playlist songs (playlist items).
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/RAPPAR/
 *
 * @property PlaylistModel $PlaylistModel
 * @property SongModel $SongModel
 * @property PlaylistSongModel $PlaylistSongModel
 * @property UtilityModel $UtilityModel
 * @property LogModel $LogModel
 * @property SecurityModel $SecurityModel
 * @property RefreshPlaylistService $RefreshPlaylistService
 * @property CI_DB_mysqli_driver $db
 * @property CI_Input $input
 */
class PlaylistItems extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('PlaylistModel');
        $this->load->model('SongModel');
        $this->load->model('PlaylistSongModel');
        $this->load->library('RefreshPlaylistService');
        $this->load->library('InsertSongService');
        $this->RefreshPlaylistService = new RefreshPlaylistService();
        $this->InsertSongService = new InsertSongService();
    }

    /**
     * This method handles the normal loading of playlist items
     * By default all songs in the playlists are loaded
     * If filters are used, only the songs that match the filter are shown
     * Search query is a text-based filter that is empty by default and not
     *      included as a separate filter (those are based on checkboxes)
     *
     * The specific filters are:
     * repeat - only show songs with the repeat checkbox checked
     * unrated - only show songs that are unrated by at least one reviewer
     * checkbox property - only show songs with the specified checkbox property checked
     *
     * @return void
     */
    public function loadPlaylist(): void
    {
        //Validate the provided playlist id
        $data['listId'] = filter_var($this->input->get('playlistId'), FILTER_VALIDATE_INT);
        if (!$data['listId'])
            redirect('errors/403-404');

        //Confirm a playlist with this id exists
        $data['playlist'] = $this->PlaylistModel->fetchPlaylistById($data['listId']);
        if ($data['playlist'] === false)
            redirect('errors/403-404');

        //Define user permissions
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        $userId = $this->SecurityModel->getCurrentUserId();
        $playlistOwnerId = $this->PlaylistModel->getListOwnerById($data['listId']);
        $data['isPlaylistOwner'] = $playlistOwnerId === $userId;
        $data['rapparManagedPlaylist'] = $playlistOwnerId === 1;

        //Unless the playlist is public, it can only be accessed by its owner
        $userAuthorised = ($userAuthenticated && $data['isPlaylistOwner']) || $data['playlist']->ListPublic;
        if (!$userAuthorised)
            redirect('errors/403-404');

        $data['searchQuery'] = $this->input->get('searchQuery') ?? '';
        $data['isReviewer'] = $this->SecurityModel->authenticateReviewer();
        $data['title'] = "Playlista ".$data['playlist']->ListName." | Oceniaj i komentuj utwory";
        $data['userOwnedPlaylists'] = $this->PlaylistModel->getUserPlayistsIdsAndNames($userId);
        $data['body'] = 'playlist/insidePlaylist/playlist';
        $data['songs'] = [];

        //Define various checkbox-related properties in one place - first the db name, then the button name, then the display name
        $data['checkboxPropertiesDetails'] = [
            ['SongDistinction', 'btnDistinction', 'Wyróżnienie'],
            ['SongMemorial', 'btnMemorial', '10*'],
            ['SongXD', 'btnXD', 'XD'],
            ['SongNotRap', 'btnNotRap', 'To Nie Rapsik'],
            ['SongDiscomfort', 'btnDiscomfort', 'Strefa Dyskomfortu'],
            ['SongTop', 'btnTop', 'X15'],
            ['SongNoGrade', 'btnNoGrade', 'Nie Oceniam'],
            ['SongUber', 'btnUber', 'Uber'],
            ['SongBelow', 'btnBelowSeven', '< 7'],
            ['SongBelTen', 'btnBelowTen', '< 10'],
            ['SongBelNine', 'btnBelowNine', '< 9'],
            ['SongBelEight', 'btnBelowEight', '< 8'],
            ['SongBelFour', 'btnBelowFour', '< 4'],
            ['SongDuoTen', 'btnDuoTen', '"10"'],
            ['SongVeto', 'btnVeto', 'VETO'],
            ['SongBelHalfSeven', 'btnBelowHalfSeven', '< 7.5'],
            ['SongBelHalfEight', 'btnBelowHalfEight', '< 8.5'],
            ['SongBelHalfNine', 'btnBelowHalfNine', '< 9.5'],
            ['SongRehearsal', 'btnRehearsal', 'Ponowny Odsłuch']
        ];

        //Apply a filter if one was selected by the user
        $filter = $this->input->get('filter');
        switch ($filter) {
            case "Checkbox":
            {
                //A 'checkbox selected' filter is in use
                $checkboxProperty = $this->input->get('prop');
                foreach ($data['checkboxPropertiesDetails'] as $checkboxProperties) {
                    if (in_array($checkboxProperty, $checkboxProperties)) {
                        $data['songs'] = $this->PlaylistSongModel->filterSongsByCheckboxProperty($data['listId'], $checkboxProperty);
                        break;
                    }
                }
                break;
            }
            case "Unrated":
            {
                //The 'unrated' filter is in use
                $data['songs'] = $this->PlaylistSongModel->filterUnrated($data['listId']);
                break;
            }
            default:
            {
                //Fetch songs and filter by the search query if one was used
                $data['songs'] = $this->PlaylistSongModel->getPlaylistSongs($data['listId'], $data['searchQuery']);
                break;
            }
        }

        //Compute the average grades song-by-song
        $avgOverall = 0;
        $ratedTotal = 0;
        $avgAdam = 0;
        $avgChurchie = 0;
        $ratedAdam = 0;
        $ratedChurchie = 0;
        foreach ($data['songs'] as $song) {
            //Display values without decimals at the end if the decimals are zeros
            $this->setSongGrades($song, $data['rapparManagedPlaylist']);

            //Check per-reviewer averages to add to the playlist statistics
            $includeAdam = $song->SongGradeAdam > 0 && !($song->SongDuoTen && $song->SongGradeAdam == 10);
            $includeChurchie = $song->SongGradeChurchie > 0 && !($song->SongDuoTen && $song->SongGradeChurchie == 10);
            $includeOwner = $song->SongGradeOwner > 0 && !($song->SongDuoTen && $song->SongGradeOwner == 10);
            if ($includeAdam || $includeChurchie) {
                $avgAdam += $includeAdam ? $song->SongGradeAdam : 0;
                $avgChurchie += $includeChurchie ? $song->SongGradeChurchie : 0;
                $ratedAdam += $includeAdam ? 1 : 0;
                $ratedChurchie += $includeChurchie ? 1 : 0;
            }

            if ($includeAdam || $includeChurchie || $includeOwner) {
                $avgOverall += $song->Average;
                $ratedTotal += 1;
            }
        }

        //Calculate playlist averages for each reviewer
        $data['avgAdam'] = $ratedAdam > 0 ? $avgAdam / $ratedAdam : 0;
        $data['avgChurchie'] = $ratedChurchie > 0 ? $avgChurchie / $ratedChurchie : 0;
        $data['ratedAdam'] = $ratedAdam;
        $data['ratedChurchie'] = $ratedChurchie;
        $data['avgOverall'] = $ratedTotal > 0 ? $avgOverall / $ratedTotal : 0;
        $data['ratedOverall'] = $ratedTotal;

        $this->load->view('templates/customNav', $data);
    }

    /**
     * This method handles global search queries
     * Global search only works on the homepage
     * Global search looks into public playlists and your playlists
     *
     * @return void
     */
    public function globalSearch(): void
    {
        $data = array(
            'isReviewer' => $this->SecurityModel->authenticateReviewer(),
            'searchQuery' => $this->input->get('SearchQuery'),
            'body' => 'playlist/insidePlaylist/searchResults',
            'title' => "Wyniki wyszukiwania | Wyszukiwanie odbywa się w twoich i należących do RAPPAR playlistach",
            'songs' => array(),
            'playlist' => array(),
            'userId' => $this->SecurityModel->getCurrentUserId()
        );

        //Only fetch songs if the query is valid
        if (strlen($data['searchQuery']) > 0) {
            $data['songs'] = $this->PlaylistSongModel->getPlaylistSongsFromSearch($data['searchQuery']);
            if (count($data['songs']) > 0 && count($data['songs']) < 301) {
                $data['userOwnedPlaylists'] = $this->PlaylistModel->getUserPlayistsIdsAndNames($data['userId']);
                $data['userOwnedPlaylistIDs'] = array_map(fn($item) => $item->ListId, $this->PlaylistModel->fetchUserPlaylistsIDs($data['userId']));
                foreach ($data['songs'] as $song) {
                    //Display values without ending decimals if they're only 0's (ex. 5.50 -> 5.5)
                    $this->setSongGrades($song);

                    //Get song button information
                    $data['playlist'][] = $this->PlaylistModel->fetchPlaylistById($song->listId);
                }
            }
        }

        $this->load->view('templates/customNav', $data);
    }

    /**
     * This method handles tierlists
     * Tierlists can be accessed from playlists
     * Tierlists can be generated based on the reviewers' scores, your scores, or average scores
     *
     * @return void
     */
    public function tierlist(): void
    {
        //Verify whether a valid playlist id was submitted
        $data['listId'] = filter_var($this->input->get('playlistId'), FILTER_VALIDATE_INT);
        if (!$data['listId'])
            redirect('errors/403-404');

        //Confirm a playlist with this id exists
        $data['playlist'] = $this->PlaylistModel->fetchPlaylistById($data['listId']);
        if ($data['playlist'] === false)
            redirect('errors/403-404');

        //Define user permissions
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        $userId = $this->SecurityModel->getCurrentUserId();
        $playlistOwnerId = $this->PlaylistModel->getListOwnerById($data['listId']);
        $data['isPlaylistOwner'] = $playlistOwnerId === $userId;
        $data['rapparManagedPlaylist'] = $playlistOwnerId === 1;

        //Unless the playlist is public, it can only be accessed by its owner
        $userAuthorised = ($userAuthenticated && $data['isPlaylistOwner']) || $data['playlist']->ListPublic;
        if (!$userAuthorised)
            redirect('errors/403-404');

        //Fetch other relevant data to complete the tierlist
        $data['filter'] = $this->input->get('filter');
        $data['isReviewer'] = $this->SecurityModel->authenticateReviewer();
        $data['userOwnedPlaylists'] = $this->PlaylistModel->getUserPlayistsIdsAndNames($userId);
        $data['propName'] = $data['filter'] === "Adam" ? "SongGradeAdam" : ($data['filter'] === "Churchie" ? "SongGradeChurchie" : ($data['filter'] === "Owner" ? "SongGradeOwner" : "Average"));
        $data['songs'] = $this->PlaylistSongModel->getTopPlaylistSongs($data['listId'], $data['filter'], $data['rapparManagedPlaylist']);
        $data['title'] = "Playlista ".$data['playlist']->ListName." | Oceniaj i komentuj utwory";
        $data['body'] = 'playlist/insidePlaylist/tierlist';

        //Pre-compute every song's average and trim trailing zeros
        foreach ($data['songs'] as $song) {
            $this->setSongGrades($song);
        }

        //Filter out songs without the reviewer's grade
        $data['songs'] = array_filter($data['songs'], function ($song) use ($data) {
            return $song->{$data['propName']} >= 1 && $song->{$data['propName']} <= 15;
        });

        //Sort the filtered array by the reviewer's grade in descending order
        usort($data['songs'], function ($a, $b) use ($data) {
            return $b->{$data['propName']} <=> $a->{$data['propName']};
        });

        $this->load->view('templates/customNav', $data);
    }

    /**
     * This method updates song ratings inside a playlist
     * Song ratings include: reviewer grades, personal grades, checkbox checks, and review notes
     * If 'transfer song' or 'copy song to another playlist' were queued, they will be processed here
     *
     * This method is called when the user clicks the 'Zapisz Oceny' button
     * The button is available in the playlist and tierlist views
     *
     * Since each song, of which there may be hundreds, has almost 30 individual features,
     *  a JS script (playlist.js) checks if any updates were made before processing the features
     *  via an input type hidden in the form
     *
     * @return void
     */
    public function updateSongRatingsInPlaylist(): void
    {
        //Validate the submitted playlist id
        $data['playlistId'] = filter_var($this->input->post('playlistId'), FILTER_VALIDATE_INT);
        $playlist = $data['playlistId'] !== false ? $this->PlaylistModel->fetchPlaylistById($data['playlistId']) : false;
        if (!$data['playlistId'] || !$playlist)
            redirect('errors/403-404');

        //Check if a user is logged in
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        $userId = $this->SecurityModel->getCurrentUserId();
        if ($userId === false || !$userAuthenticated)
            redirect("logout");

        //The user must be the owner of the playlist they are updating grades in
        $playlistOwnerId = $this->PlaylistModel->getListOwnerById($data['playlistId']);
        $userAuthorised = $playlistOwnerId == $userId;
        if (!$userAuthorised)
            redirect('errors/403-404');

        //Process each song separately
        $i = 0;
        $resultMessage = "<pre>";
        $data['searchQuery'] = false;
        $data['processedSongsCount'] = 0;
        $data['processedAndUpdatedSongsCount'] = 0;
        $data['title'] = 'Zapisano oceny w playliście '.$playlist->ListName.'!';
        $data['body'] = 'playlistSong/updatePlaylistSongRatings';
        $data['saveSource'] = $this->input->get('src');
        $data['filter'] = $this->input->get('filter');
        $data['userLoggedIn'] = true;
        $data['isReviewer'] = $this->SecurityModel->authenticateReviewer();
        while (isset($_POST["songUpdated-".$i+21])) {
            //Count the number of songs flagged as requiring update
            $i += ($data['processedSongsCount'] == 0) ? 0 : 28;
            $data['processedSongsCount'] += 1;

            //Only process songs that were actually updated
            $songUpdated = $this->input->post('songUpdated-'.$i+21);
            if ($songUpdated) {
                //Fetch the received songs and make sure they exist
                $formInput['playlistSongId'] = $this->input->post('playlistSongId-'.$i);
                $currentPlaylistSong = $this->PlaylistSongModel->getPlaylistSong($formInput['playlistSongId']);
                $currentSong = $currentPlaylistSong !== false ? $this->SongModel->getSong($currentPlaylistSong->songId) : false;
                if ($currentPlaylistSong !== false) {
                    //Update song grades, buttons and the song comment
                    $localResultMessage = $this->updateBasicPlaylistSongDetails($formInput, $currentPlaylistSong, $i);

                    //Create a song update log if any changes were made
                    if (!empty($localResultMessage)) {
                        $data['processedAndUpdatedSongsCount'] += 1;
                        $this->LogModel->createLog('playlist_song', $currentPlaylistSong->id, "Zapisano oceny nuty z playlisty");
                    }

                    //Proceed if a matching song exists
                    if ($currentSong !== false) {
                        //Copy and move songs between playlists
                        $resultMessage .= $this->copyAndMovePlaylistSong($formInput, $userId, $currentPlaylistSong, $localResultMessage, $currentSong->SongTitle, "playlisty", $data);

                        //Update reviewers' grades
                        if ($playlistOwnerId == 1 && ($formInput['SongGradeAdam'] != $currentSong->SongGradeAdam || $formInput['SongGradeChurchie'] != $currentSong->SongGradeChurchie))
                            $this->SongModel->updateReviewerRatings($currentSong->SongId, $formInput['SongGradeAdam'], $formInput['SongGradeChurchie']);
                    }
                }
                else
                    $resultMessage .= "<br><br>\tNie znaleziono utworu o ID ".$formInput['playlistSongId']."<br><br>";
            }
        }
        //Finalise the result message and submit it as a report
        $data['resultMessage'] = $resultMessage . "</pre>";
        $newReportId = $this->LogModel->submitReport(htmlspecialchars($data['resultMessage']));

        //Create a log
        $this->LogModel->createLog('playlist', $data['playlistId'], "Zapisano oceny z playlisty i dołączono raport.", $newReportId);

        $this->load->view('templates/main', $data);
    }

    /**
     * Update playlist_song grades through the search engine.
     *
     * @return void
     */
    public function updateGradesFromSearch(): void
    {
        //Check if the user is logged in
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        $userId = $this->SecurityModel->getCurrentUserId();
        if ($userId === false || !$userAuthenticated)
            redirect("logout");

        //To update songs through the search engine, the user must own at least one playlist
        $userOwnedPlaylists = array_map(fn($item) => $item->ListId, $this->PlaylistModel->fetchUserPlaylistsIDs($userId));
        $updateAuthenticated = count($userOwnedPlaylists) > 0;
        if (!$updateAuthenticated)
            redirect('errors/403-404');

        $data = array(
            'body' => 'playlistSong/updatePlaylistSongRatings',
            'title' => 'Wynik zapisu ocen z wyszukiwarki',
            'searchQuery' => $this->input->post('searchQuery'),
            'userLoggedIn' => true,
            'isReviewer' => $this->SecurityModel->authenticateReviewer()
        );
        $resultMessage = "<pre>";

        //Process each song separately
        $i = 0;
        $data['processedSongsCount'] = 0;
        $updatedSongs = [];
        while (isset($_POST['songUpdated-'.$i+21])) {
            //Count the number of songs flagged as requiring update
            $i += ($data['processedSongsCount'] == 0) ? 0 : 28;
            $data['processedSongsCount'] += 1;

            //Only process songs that were actually updated
            if ($this->input->post('songUpdated-' . $i + 21)) {
                //Fetch the received songs and make sure they exist
                $formInput['playlistSongId'] = $this->input->post('playlistSongId-'.$i);
                $currentPlaylistSong = $this->PlaylistSongModel->getPlaylistSong($formInput['playlistSongId']);
                $currentSong = $currentPlaylistSong !== false ? $this->SongModel->getSong($currentPlaylistSong->songId) : false;
                if ($currentPlaylistSong !== false && $currentSong !== false) {
                    //Check if the user is allowed to update this playlist song (through search or playlist listing)
                    $updateAuthorised = in_array($currentPlaylistSong->listId, $userOwnedPlaylists);
                    if (!$updateAuthorised)
                        redirect('errors/403-404');

                    //Update song grades, buttons and the song comment
                    $localResultMessage = $this->updateBasicPlaylistSongDetails($formInput, $currentPlaylistSong, $i);

                    //Store the id of the updated song to later write a log with an attached report
                    if (!empty($localResultMessage))
                        $updatedSongs[] = $currentPlaylistSong->id;

                    //Copy and move songs between playlists
                    $resultMessage .= $this->copyAndMovePlaylistSong($formInput, $userId, $currentPlaylistSong, $localResultMessage, $currentSong->SongTitle, "wyszukiwarki", $data);
                }
                else
                    $resultMessage .= "<br><br>\tNie znaleziono utworu o ID ".$formInput['playlistSongId']."<br><br>";
            }
        }

        //Finalise the result message and submit it as a report
        $data['processedAndUpdatedSongsCount'] = count($updatedSongs);
        $data['resultMessage'] = $resultMessage."</pre>";
        $newReportId = $this->LogModel->submitReport(htmlspecialchars($data['resultMessage']));

        //Create a log for each updated song
        foreach ($updatedSongs as $updatedSongId)
            $this->LogModel->createLog('playlist_song', $updatedSongId, "Zapisano oceny z wyszukiwarki i dołączono raport.", $newReportId);

        $this->load->view('templates/main', $data);
    }

    /**
     * Updates the playlist with new songs added to it on YouTube.
     *
     * @return void
     */
    public function downloadSongs(): void
    {
        //Fetch the submitted playlist id
        $listId = $this->input->get('playlistId') ?? 0;
        $listId = is_numeric($listId) ? $listId : 0;
        $data = array(
            'body' => 'playlistSong/downloadPlaylistSongs',
            'title' => 'Wyniki aktualizacji playlisty',
            'listId' => $listId,
            'src' => $this->input->get('src'),
            'userLoggedIn' => true,
            'isReviewer' => $this->SecurityModel->authenticateReviewer()
        );

        //Check if the user is logged in and has the required permissions
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        $userId = $this->SecurityModel->getCurrentUserId();
        $userAuthorised = $userAuthenticated && $this->PlaylistModel->getListOwnerById($listId) == $userId;
        if ($userAuthorised) {
            //Check if the playlist has a YouTube link.
            $playlistUrl = $this->PlaylistModel->getListUrlById($data['listId']);
            if (!empty($playlistUrl)) {
                //Refresh the playlist - if everything went well, the message will be empty
                $data['displayErrorMessage'] = $this->RefreshPlaylistService->refreshPlaylist($data['listId'], $userId);
            }
            else
                $data['displayErrorMessage'] = "Nie znaleziono linku do tej playlisty na YT!";

            $this->load->view('templates/main', $data);
        }
        else redirect('errors/403-404');
    }

    /**
     * Allows the user to hide (privatise) or show (publicise) an existing playlist song.
     *
     * @return void
     */
    public function updatePlaylistSongVisibility(): void
    {
        //Validate the submitted song id
        $playlistSongId = isset($_GET['songId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['songId'])) : 0;
        $playlistSong = is_numeric($playlistSongId) ? $this->PlaylistSongModel->getPlaylistSong($playlistSongId) : false;
        if ($playlistSong !== false) {
            //Check if the user is logged in and has the required permissions
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = $userAuthenticated && $this->PlaylistModel->getListOwnerById($playlistSong->listId) == $this->SecurityModel->getCurrentUserId();
            if ($userAuthorised) {
                //Update song visibility
                $currentVisibility = $playlistSong->SongVisible;
                $newVisibility = $currentVisibility == 1 ? 0 : 1;

                $this->PlaylistSongModel->updatePlaylistSongVisibility($playlistSongId, $newVisibility);
                $this->LogModel->createLog('playlist_song', $playlistSongId, ($newVisibility ? "Upubliczniono" : "Ukryto")." nutę na playliście");

                //Return to the playlist details view
                $redirectSource = isset($_GET['src']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['src'])) : 0;
                if ($redirectSource == 'pd')
                    redirect('playlist/details?playlistId='.$playlistSong->listId.'&src=pd');
                else redirect('playlist/details?playlistId='.$playlistSong->listId.'&src=mp');
            }
            else redirect('errors/403-404');
        }
        else redirect('errors/403-404');
    }

    /**
     * Allows the user to delete a song from a playlist.
     *
     * @return void
     */
    public function deletePlaylistSong(): void
    {
        //Validate the posted playlist song id
        $playlistSongId = $this->input->get('songId');
        $playlistSong = $playlistSongId ? $this->PlaylistSongModel->getPlaylistSong($playlistSongId) : false;
        if ($playlistSong !== false) {
            //Check if the user is logged in and has the required permissions
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userId = $this->SecurityModel->getCurrentUserId();
            $userAuthorised = $userAuthenticated && $this->PlaylistModel->getListOwnerById($playlistSong->listId) == $userId;
            if ($userAuthorised) {
                $data = array(
                    'body' => 'song/delSong',
                    'playlist' => $this->PlaylistModel->fetchPlaylistById($playlistSong->listId),
                    'redirectSource' => $this->input->get('src'),
                    'song' => $this->SongModel->getSong($playlistSong->songId),
                    'playlistSong' => $playlistSong,
                    'userLoggedIn' => true,
                    'isReviewer' => $this->SecurityModel->authenticateReviewer()
                );
                $data['title'] = 'Usuń utwór '.$data['song']->SongTitle.' z playlisty '.$data['playlist']->ListName.' | Ta decyzja jest nieodwracalna';

                //Delete the song if the form was submitted
                $delSong = $this->input->get('delete');
                if ($delSong) {
                    $this->LogModel->createLog('playlist_song', $playlistSongId, "Permanentnie usunięto nutę z playlisty.");
                    $this->LogModel->createLog('playlist', $data['playlistSong']->listId, "Permanentnie usunięto nutę " .$data['song']->SongTitle. " z playlisty.");
                    $this->PlaylistSongModel->deletePlaylistSong($playlistSongId);
                    if ($data['redirectSource'] == 'pd')
                        redirect('playlist/details?listId='.$data['playlistSong']->listId.'&src=pd');
                    else redirect('playlist/details?listId='.$data['playlistSong']->listId.'&src=mp');
                }

                $this->load->view('templates/main', $data);
            }
            else redirect('errors/403-404');
        }
        else redirect('errors/403-404');
    }

    /**
     * The function takes the form inputs with song details and compares them with the actual song
     * If the two overlap, the song is identical and an empty array is returned
     * If even one of the values is different, the songs are different, and an array
     * of the differing properties is returned
     *
     * @param $currentSong object the current state of the song
     * @param $formInput array the state of the song after the form is submitted
     * @return array array of property names to update
     */
    public function flagSongDataToUpdate(object $currentSong, array $formInput): array
    {
        //Set update flags
        $flags['SongGradeAdam'] = $currentSong->SongGradeAdam != $formInput['SongGradeAdam'];
        $flags['SongGradeChurchie'] = $currentSong->SongGradeChurchie != $formInput['SongGradeChurchie'];
        $flags['SongGradeOwner'] = $currentSong->SongGradeOwner != $formInput['SongGradeOwner'];
        $flags['SongRehearsal'] = $currentSong->SongRehearsal != $formInput['SongRehearsal'];
        $flags['SongDistinction'] = $currentSong->SongDistinction != $formInput['SongDistinction'];
        $flags['SongMemorial'] = $currentSong->SongMemorial != $formInput['SongMemorial'];
        $flags['SongXD'] = $currentSong->SongXD != $formInput['SongXD'];
        $flags['SongNotRap'] = $currentSong->SongNotRap != $formInput['SongNotRap'];
        $flags['SongDiscomfort'] = $currentSong->SongDiscomfort != $formInput['SongDiscomfort'];
        $flags['SongDepA'] = $currentSong->SongDepA != $formInput['SongDepA'];
        $flags['SongTop'] = $currentSong->SongTop != $formInput['SongTop'];
        $flags['SongNoGrade'] = $currentSong->SongNoGrade != $formInput['SongNoGrade'];
        $flags['SongUber'] = $currentSong->SongUber != $formInput['SongUber'];
        $flags['SongBelow'] = $currentSong->SongBelow != $formInput['SongBelow'];
        $flags['SongBelTen'] = $currentSong->SongBelTen != $formInput['SongBelTen'];
        $flags['SongBelNine'] = $currentSong->SongBelNine != $formInput['SongBelNine'];
        $flags['SongBelEight'] = $currentSong->SongBelEight != $formInput['SongBelEight'];
        $flags['SongBelFour'] = $currentSong->SongBelFour != $formInput['SongBelFour'];
        $flags['SongDuoTen'] = $currentSong->SongDuoTen != $formInput['SongDuoTen'];
        $flags['SongVeto'] = $currentSong->SongVeto != $formInput['SongVeto'];
        $flags['SongComment'] = $currentSong->SongComment != $formInput['SongComment'];
        $flags['SongBelHalfSeven'] = $currentSong->SongBelHalfSeven != $formInput['SongBelHalfSeven'];
        $flags['SongBelHalfEight'] = $currentSong->SongBelHalfEight != $formInput['SongBelHalfEight'];
        $flags['SongBelHalfNine'] = $currentSong->SongBelHalfNine != $formInput['SongBelHalfNine'];

        //If any of the flags is set (true), add it to the list
        if (in_array(true, $flags, true)) {
            $triggeredFlags = [];
            foreach ($flags as $key => $flag) {
                if ($flag)
                    $triggeredFlags[] = $key;
            }
            return $triggeredFlags;
        }
        else return [];
    }

    /**
     * Given an internal property name, returns a custom display name.
     * Applies only to checkbox properties.
     *
     * @param $propertyName string the internal name of the property
     * @return string the custom display name of the property
     */
    public function getPropertyDisplayName(string $propertyName): string
    {
        return match ($propertyName) {
            'SongRehearsal' => "ponowny odsłuch",
            'SongDistinction' => "wyróżnienie",
            'SongMemorial' => "10*",
            'SongXD' => "XD",
            'SongNotRap' => "to nie rapsik",
            'SongDiscomfort' => "strefa dyskomfortu",
            'SongDepA' => "department abroad",
            'SongTop' => "X15",
            'SongNoGrade' => "nie oceniam",
            'SongUber' => "Uber",
            'SongBelow' => "< 7",
            'SongBelTen' => "< 10",
            'SongBelNine' => "< 9",
            'SongBelEight' => "< 8",
            'SongBelFour' => "< 4",
            'SongDuoTen' => '"10"',
            'SongVeto' => "VETO",
            'SongBelHalfSeven' => "< 7.5",
            'SongBelHalfEight' => "< 8.5",
            'SongBelHalfNine' => "< 9.5",
            default => ""
        };
    }

    /**
     * Compute the average song score based on the reviewers' grades.
     *
     * @param object $song song to calculate the average for
     * @return float|int the average calculated
     */
    public function calculateAverage(object $song): float|int
    {
        $grades = [
            $song->SongGradeAdam,
            $song->SongGradeChurchie
        ];

        //Filter out zero values
        $nonZeroGrades = array_filter($grades, function ($grade) {
            return $grade > 0;
        });

        //Calculate the average if there are non-zero grades, otherwise return 0
        return count($nonZeroGrades) > 0 ? round(array_sum($nonZeroGrades) / count($nonZeroGrades), 2) : 0;
    }

    /**
     * Format playlist_song grades and compute the average.
     * If the playlist is managed by Rappar, the playlist is the average of the two reviewers.
     * If if is managed by regular users, the only score becomes the song average.
     *
     * @param object $song playlist song to set the grades for
     * @param bool $rapparManagedPlaylist true if the playlist is managed by rappar
     * @return void the updated playlist song object
     */
    public function setSongGrades(object $song, bool $rapparManagedPlaylist = false): void
    {
        $song->SongGradeAdam = $this->UtilityModel->trimTrailingZeroes($song->SongGradeAdam);
        $song->SongGradeChurchie = $this->UtilityModel->trimTrailingZeroes($song->SongGradeChurchie);
        $song->SongGradeOwner = $this->UtilityModel->trimTrailingZeroes($song->SongGradeOwner);
        $song->Average = $rapparManagedPlaylist ? $this->calculateAverage($song) : $song->SongGradeOwner;
    }

    /**
     * Fetch form data and compare it with the existing playlist song data.
     * Update song grades, checkboxes and the comment if any changes were made.
     *
     * @param array $formInput
     * @param object $currentPlaylistSong
     * @param int $i
     * @return string return the update message and (by reference) retrieved form inputs.
     */
    public function updateBasicPlaylistSongDetails(array &$formInput, object $currentPlaylistSong, int $i): string
    {
        //Fetch playlist song grades
        $formInput['SongGradeAdam'] = $this->input->post("newGradeAdam-".$i+1) ?? $currentPlaylistSong->SongGradeAdam;
        $formInput['SongGradeChurchie'] = $this->input->post("newGradeChurchie-".$i+2) ?? $currentPlaylistSong->SongGradeChurchie;
        $formInput['SongGradeOwner'] = $this->input->post("myNewGrade-".$i+27) ?? $currentPlaylistSong->SongGradeOwner;

        //Fetch playlist song buttons (checkboxes)
        $formInput['SongRehearsal'] = $this->input->post("songRehearsal-".$i+4) ?? $currentPlaylistSong->SongRehearsal;
        $formInput['SongDistinction'] = $this->input->post("songDistinction-".$i+5) ?? $currentPlaylistSong->SongDistinction;
        $formInput['SongMemorial'] = $this->input->post("songMemorial-".$i+6) ?? $currentPlaylistSong->SongMemorial;
        $formInput['SongXD'] = $this->input->post("songXD-".$i+7) ?? $currentPlaylistSong->SongXD;
        $formInput['SongNotRap'] = $this->input->post("songNotRap-".$i+8) ?? $currentPlaylistSong->SongNotRap;
        $formInput['SongDiscomfort'] = $this->input->post("songDiscomfort-".$i+9) ?? $currentPlaylistSong->SongDiscomfort;
        $formInput['SongDepA'] = $this->input->post("songDepA-".$i+26) ?? $currentPlaylistSong->SongDepA;
        $formInput['SongTop'] = $this->input->post("songTop-".$i+10) ?? $currentPlaylistSong->SongTop;
        $formInput['SongNoGrade'] = $this->input->post("songNoGrade-".$i+11) ?? $currentPlaylistSong->SongNoGrade;
        $formInput['SongUber'] = $this->input->post("songUber-".$i+12) ?? $currentPlaylistSong->SongUber;
        $formInput['SongBelow'] = $this->input->post("songBelow-".$i+13) ?? $currentPlaylistSong->SongBelow;
        $formInput['SongBelTen'] = $this->input->post("songBelTen-".$i+14) ?? $currentPlaylistSong->SongBelTen;
        $formInput['SongBelNine'] = $this->input->post("songBelNine-".$i+15) ?? $currentPlaylistSong->SongBelNine;
        $formInput['SongBelEight'] = $this->input->post("songBelEight-".$i+16) ?? $currentPlaylistSong->SongBelEight;
        $formInput['SongBelFour'] = $this->input->post("songBelFour-".$i+17) ?? $currentPlaylistSong->SongBelFour;
        $formInput['SongDuoTen'] = $this->input->post("songDuoTen-".$i+18) ?? $currentPlaylistSong->SongDuoTen;
        $formInput['SongVeto'] = $this->input->post("songVeto-".$i+19) ?? $currentPlaylistSong->SongVeto;
        $formInput['SongBelHalfSeven'] = $this->input->post("SongBelHalfSeven-".$i+23) ?? $currentPlaylistSong->SongBelHalfSeven;
        $formInput['SongBelHalfEight'] = $this->input->post("SongBelHalfEight-".$i+24) ?? $currentPlaylistSong->SongBelHalfEight;
        $formInput['SongBelHalfNine'] = $this->input->post("SongBelHalfNine-".$i+25) ?? $currentPlaylistSong->SongBelHalfNine;

        //Move and copy song select boxes
        $formInput['newPlaylistId'] = $this->input->post("newPlaylistId-".$i+3);
        $formInput['copyToPlaylist'] = $this->input->post("copyPlaylistId-".$i+20);

        //Song comment textarea
        $formInput['SongComment'] = $this->input->post("songComment-".$i+22) ?? $currentPlaylistSong->SongComment;

        //Get a list properties that need updating by comparing the form input data with the current song data
        $elementsToUpdate = $this->flagSongDataToUpdate($currentPlaylistSong, $formInput);

        //Update song grades
        $localResultMessage = "";
        $adamGradeUpdated = in_array("SongGradeAdam", $elementsToUpdate);
        $churchieGradeUpdated = in_array("SongGradeChurchie", $elementsToUpdate);
        $ownerGradeUpdated = in_array("SongGradeOwner", $elementsToUpdate);
        if ($adamGradeUpdated || $churchieGradeUpdated || $ownerGradeUpdated) {
            //Some countries use comma and not the dot when expressing numbers. Ensure consistency by replacing commas with dots.
            $newAdamRating = str_replace(',', '.', $formInput['SongGradeAdam']);
            $newChurchieRating = str_replace(',', '.', $formInput['SongGradeChurchie']);
            $newOwnerRating = str_replace(',', '.', $formInput['SongGradeOwner']);

            //Ensure the ratings are valid decimal numbers (full or .5) and are in the range <0, 15>
            if (is_numeric($newAdamRating) && is_numeric($newChurchieRating) && is_numeric($newOwnerRating)
                && $this->UtilityModel->inRange($newAdamRating, 0, 15) && $this->UtilityModel->inRange($newChurchieRating, 0, 15) && $this->UtilityModel->inRange($newOwnerRating, 0, 15)
                && fmod($newAdamRating, 0.5) == 0 && fmod($newChurchieRating, 0.5) == 0 && fmod($newOwnerRating, 0.5) == 0)
            {
                //Update the ratings and prepare the update message
                $this->PlaylistSongModel->updatePlaylistSongScores($currentPlaylistSong->id, $newAdamRating, $newChurchieRating, $newOwnerRating);
                if ($adamGradeUpdated)
                    $localResultMessage .= "\tOcena Adama: " . $currentPlaylistSong->SongGradeAdam . " -> " . $newAdamRating . "<br>";
                if ($churchieGradeUpdated)
                    $localResultMessage .= "\tOcena Kościelnego: " . $currentPlaylistSong->SongGradeChurchie . " -> " . $newChurchieRating . "<br>";
                if ($ownerGradeUpdated)
                    $localResultMessage .= "\tOcena Właściciela: " . $currentPlaylistSong->SongGradeOwner . " -> " . $newOwnerRating;
            }
        }

        //Update the song comment
        $commentUpdated = in_array("SongComment", $elementsToUpdate);
        if ($commentUpdated) {
            $this->PlaylistSongModel->updateSongComment($currentPlaylistSong->id, $formInput['SongComment']);
            $localResultMessage .= ($localResultMessage == "" ? "\t" : "<br>\t");
            $localResultMessage .= "Komentarz: " . $currentPlaylistSong->SongComment . " -> " . $formInput['SongComment'];
        }

        //Update song checkbox properties (buttons)
        foreach ($elementsToUpdate as $prop) {
            if (!in_array($prop, ["SongGradeAdam", "SongGradeChurchie", "SongGradeOwner", "SongComment"])) {
                $propertyDisplayName = $this->getPropertyDisplayName($prop);
                $this->PlaylistSongModel->updateSongCheckboxProperty($currentPlaylistSong->id, $prop, $formInput[$prop]);
                $localResultMessage .= ($localResultMessage == "" ? "\t" : "<br>\t");
                $localResultMessage .= ($formInput[$prop] ? "Zaznaczono " : "Odznaczono ") . $propertyDisplayName;
            }
        }

        return $localResultMessage;
    }

    /**
     * Process copying and moving songs between playlists.
     *
     * @param array $formInput
     * @param int $userId
     * @param object $currentPlaylistSong
     * @param string $localResultMessage
     * @param string $songTitle
     * @param string $location whether the request came from the search engine or a playlist
     * @param array $data
     * @return string return the update message and (by reference) error messages.
     */
    public function copyAndMovePlaylistSong(array $formInput, int $userId, object $currentPlaylistSong, string $localResultMessage, string $songTitle, string $location, array &$data): string
    {
        //Copy songs to local playlists
        $newPlaylistSongId = 0;
        if ($formInput['copyToPlaylist']) {
            //Make sure the user is the owner of the playlist they are copying to
            if ($this->PlaylistModel->getListOwnerById($formInput['copyToPlaylist']) == $userId) {
                $newPlaylistSongId = $this->PlaylistSongModel->copyToAnotherPlaylist($currentPlaylistSong->id, $formInput['copyToPlaylist']);
                $targetName = $this->PlaylistModel->getPlaylistNameById($formInput['copyToPlaylist']);
                $localResultMessage .= ($localResultMessage == "" ? "\t" : "<br>\t");
                $localResultMessage .= "Skopiowano do: ".$targetName;
                $this->LogModel->createLog("playlist_song", $newPlaylistSongId, "Nuta skopiowana z ".$location." do ".$targetName);
            }
        }

        //Move songs between integrated playlists
        $moveRequired = $formInput['newPlaylistId'] != 0 && $formInput['newPlaylistId'] != $currentPlaylistSong->listId;
        if ($moveRequired)
            $data['displayErrorMessage'] = $this->InsertSongService->moveSongBetweenIntegratedPlaylists(false, $currentPlaylistSong, $formInput['newPlaylistId'], $localResultMessage);

        //Copy songs to integrated playlists
        $copyRequired = $formInput['copyToPlaylist'] != 0 && $formInput['copyToPlaylist'] != $currentPlaylistSong->listId;
        $copyToIntegratedRequired = $copyRequired && $this->PlaylistModel->getPlaylistIntegratedById($formInput['copyToPlaylist']);
        if ($copyToIntegratedRequired && $newPlaylistSongId)
            $data['displayErrorMessage'] = ($data['displayErrorMessage'] ?? "")."<br>".$this->InsertSongService->copySongToIntegratedPlaylist($currentPlaylistSong, $formInput['copyToPlaylist'], $newPlaylistSongId, $localResultMessage);

        return $localResultMessage != "" ? ("<br><br>Utwór ".$songTitle.":<br><br>".$localResultMessage) : "";
    }
}
