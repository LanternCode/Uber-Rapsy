<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!isset($_SESSION))
    session_start();

/**
 * The controller responsible for handling playlist songs (playlist items).
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
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
        $this->load->model('UtilityModel');
        $this->load->model('LogModel');
        $this->load->model('SecurityModel');
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
        $data = array(
            'body' => 'playlist/insidePlaylist/playlist',
            'listId' => filter_var($this->input->get('playlistId'), FILTER_VALIDATE_INT)
        );
        if ($data['listId']) {
            //Confirm the user is authorised or the playlist is public
            $data['playlist'] = $this->PlaylistModel->fetchPlaylistById($data['listId']);
            $data['title'] = $data['playlist']->ListName . " | Playlista Uber Rapsy";
            $data['isOwner'] = isset($_SESSION['userId']) && $this->PlaylistModel->getListOwnerById($data['listId']) == $_SESSION['userId'];
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = ($userAuthenticated && $data['isOwner']) || $data['playlist']->ListPublic;
            if ($userAuthorised) {
                $data['searchQuery'] = $this->input->get('searchQuery') ?? '';
                $data['isReviewer'] = isset($_SESSION['userRole']) && $_SESSION['userRole'] == "reviewer";
                $data['allPlaylists'] = $this->PlaylistModel->getListsIdsAndNames();
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

                //Apply a filter if one was selected by the user (proceed to the default case if not)
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

                //Initialise the variables to store the average grades
                $avgAdam = 0;
                $avgChurchie = 0;
                $avgOwner = 0;
                $avgOverall = 0;
                $ratedAdam = 0;
                $ratedChurchie = 0;
                $ratedOwner = 0;
                $ratedTotal = 0;

                //Compute the average grades song-by-song
                foreach ($data['songs'] as $song) {
                    //Display values without decimals at the end if the decimals are zeros
                    $this->setSongGrades($song);

                    //Check per-reviewer averages to add to the playlist statistics
                    $includeAdam = $song->SongGradeAdam > 0 && !($song->SongDuoTen && $song->SongGradeAdam == 10);
                    $includeChurchie = $song->SongGradeChurchie > 0 && !($song->SongDuoTen && $song->SongGradeChurchie == 10);
                    $includeOwner = $song->SongGradeOwner > 0 && !($song->SongDuoTen && $song->SongGradeOwner == 10);
                    if ($includeAdam || $includeChurchie || $includeOwner) {
                        $avgAdam += $includeAdam ? $song->SongGradeAdam : 0;
                        $avgChurchie += $includeChurchie ? $song->SongGradeChurchie : 0;
                        $avgOwner += $includeOwner ? $song->SongGradeOwner : 0;
                        $ratedAdam += $includeAdam ? 1 : 0;
                        $ratedChurchie += $includeChurchie ? 1 : 0;
                        $ratedOwner += $includeOwner ? 1 : 0;
                        $avgOverall += $song->Average;
                        $ratedTotal += 1;
                    }
                }

                //Calculate playlist averages for each reviewer
                $data['avgOverall'] = $ratedTotal > 0 ? $avgOverall / $ratedTotal : 0;
                $data['avgAdam'] = $ratedAdam > 0 ? $avgAdam / $ratedAdam : 0;
                $data['avgChurchie'] = $ratedChurchie > 0 ? $avgChurchie / $ratedChurchie : 0;
                $data['avgOwner'] = $ratedOwner > 0 ? $avgOwner / $ratedOwner : 0;
                $data['ratedOverall'] = $ratedTotal;
                $data['ratedAdam'] = $ratedAdam;
                $data['ratedChurchie'] = $ratedChurchie;
                $data['ratedOwner'] = $ratedOwner;

                $this->load->view('templates/customNav', $data);
            } else redirect('logout');
        } else redirect('logout');
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
            'isReviewer' => isset($_SESSION['userRole']) && $_SESSION['userRole'] === "reviewer",
            'searchQuery' => $this->input->get('SearchQuery'),
            'body' => 'playlist/insidePlaylist/searchResults',
            'title' => "Wyniki Wyszukiwania | Uber Rapsy",
            'songs' => array(),
            'playlist' => array()
        );
        if (strlen($data['searchQuery']) > 0) {
            $data['songs'] = $this->PlaylistSongModel->getPlaylistSongsFromSearch($data['searchQuery']);
            if (count($data['songs']) > 0 && count($data['songs']) < 301) {
                $data['lists'] = $this->PlaylistModel->getListsIdsAndNames();
                $data['userOwnedPlaylists'] = isset($_SESSION['userId']) ? array_map(fn($item) => $item->ListId, $this->PlaylistModel->fetchUserPlaylistsIDs($_SESSION['userId'])) : [];
                foreach ($data['songs'] as $song) {
                    //Display values without decimals at the end if the decimals are only 0's (ex. 5.50 -> 5.5)
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
        $data = array(
            'body' => 'playlist/insidePlaylist/tierlist',
            'listId' => filter_var($this->input->get('playlistId'), FILTER_VALIDATE_INT),
            'isReviewer' => isset($_SESSION['userRole']) && $_SESSION['userRole'] === "reviewer"
        );
        if ($data['listId']) {
            //Fetch the playlist and check if the user is authorised (or the list is public)
            $data['playlist'] = $this->PlaylistModel->fetchPlaylistById($data['listId']);
            $data['title'] = $data['playlist']->ListName . " | Playlista Uber Rapsy";
            $data['isOwner'] = isset($_SESSION['userId']) && $this->PlaylistModel->getListOwnerById($data['listId']) == $_SESSION['userId'];
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = ($userAuthenticated && $data['isOwner']) || $data['playlist']->ListPublic;
            if ($userAuthorised) {
                //Define the tierlist owner
                $data['filter'] = $this->input->get('filter');
                $data['propName'] = $data['filter'] === "Adam" ? "SongGradeAdam" : ($data['filter'] === "Churchie" ? "SongGradeChurchie" : ($data['filter'] === "Owner" ? "SongGradeOwner" : "Average"));

                //Fetch other relevant data to complete the tierlist
                $data['songs'] = $this->PlaylistSongModel->getTopPlaylistSongs($data['listId'], $data['filter']);
                $data['lists'] = $this->PlaylistModel->getListsIdsAndNames();

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
            else redirect('logout');
        }
        else redirect('logout');
    }

    /**
     * This method updates song ratings inside a playlist
     * Song ratings include: reviewer grades, personal grades, checkbox checks, and review notes
     * If 'transfer song' or 'copy song to another playlist' were queued, they will be processed here
     *
     * This method is called when the user clicks the 'Zapisz Oceny' button
     * The button is available in the playlist, tierlist and searchResults views
     *
     * Since each song, of which there may be hundreds, has almost 30 individual features,
     *  a JS script (playlist.js) checks if any updates were made before processing the features
     *  via an input type hidden in the form
     *
     * @return void
     */
    public function updateSongRatingsInPlaylist(): void
    {
        //Check if the request comes from a valid playlist or the search engine
        $playlistId = filter_var($this->input->post('playlistId'), FILTER_VALIDATE_INT);
        $playlistIdValid = $playlistId == "search" || (is_int($playlistId) && $playlistId > 0);

        if ($playlistIdValid) {
            //Check if the user is allowed to update grades from the playlist or the various songs found through the search engine
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userOwnedPlaylists = $userAuthenticated ? array_map(fn($item) => $item->ListId, $this->PlaylistModel->fetchUserPlaylistsIDs($_SESSION['userId'])) : [];
            $userAuthorised = !($playlistId == "search") && $userAuthenticated && $this->PlaylistModel->getListOwnerById($playlistId) == $_SESSION['userId'];
            $searchUpdateAuthenticated = $playlistId == "search" && count($userOwnedPlaylists) > 0;
            $reviewerAuthenticated = $this->SecurityModel->authenticateReviewer();
            if ($userAuthorised || $searchUpdateAuthenticated || $reviewerAuthenticated) {
                $data = array(
                    'body' => 'updatePlaylistSongRatings',
                    'title' => 'Oceny Zapisane!',
                    'searchQuery' => $playlistId == "search" ? $this->input->post('searchQuery') : false
                );
                $resultMessage = "<pre>";

                //Fetch the playlist to access its settings
                $playlist = false;
                if ($playlistId !== "search") {
                    $playlist = $this->PlaylistModel->fetchPlaylistById($playlistId);
                    $data['playlistId'] = $playlist->ListId;
                    $reviewerAuthorised = $reviewerAuthenticated && $playlist->ListPublic;
                }

                //Process each song separately
                $i = 0;
                $data['processedSongsCount'] = 0;
                $data['processedAndUpdatedSongsCount'] = 0;
                while (isset($_POST["songUpdated-" . $i + 21])) {
                    $i += ($data['processedSongsCount'] == 0) ? 0 : 28;
                    $data['processedSongsCount'] += 1;
                    //Only process songs that were actually updated
                    $songUpdated = $this->input->post('songUpdated-'.$i+21);
                    if ($songUpdated) {
                        //Fetch the song-to-update and check whether the user is allowed to update it (through search or playlist listing)
                        $formInput['songId'] = $this->input->post('songId-'.$i);
                        $currentPlaylistSong = $this->PlaylistSongModel->getPlaylistSong($formInput['songId']);
                        $currentSong = $this->SongModel->getSong($currentPlaylistSong->songId);
                        $currentSongPlaylistPublicStatus = $this->PlaylistModel->getListPublicProperty($currentPlaylistSong->listId);
                        $updateAuthorised = $playlistId == "search" ? (in_array($currentPlaylistSong->ListId, $userOwnedPlaylists) || ($reviewerAuthenticated && $currentSongPlaylistPublicStatus)) : ($userAuthorised || $reviewerAuthorised);
                        if ($updateAuthorised) {
                            //Create a variable for the song's update message
                            $data['processedAndUpdatedSongsCount'] += 1;
                            $localResultMessage = "";

                            //Save the form data to a temp variable for easy db update - grades
                            $formInput['SongGradeAdam'] = $_POST["nwGradeA-" . $i + 1] ?? $currentPlaylistSong->SongGradeAdam;
                            $formInput['SongGradeChurchie'] = $_POST["nwGradeC-" . $i + 2] ?? $currentPlaylistSong->SongGradeChurchie;
                            $formInput['SongGradeOwner'] = $_POST["nwGradeM-" . $i + 27] ?? $currentPlaylistSong->SongGradeOwner;

                            //Buttons (checkboxes)
                            $formInput['SongRehearsal'] = $_POST["songRehearsal-" . $i + 4] ?? $currentPlaylistSong->SongRehearsal;
                            $formInput['SongDistinction'] = $_POST["songDistinction-" . $i + 5] ?? $currentPlaylistSong->SongDistinction;
                            $formInput['SongMemorial'] = $_POST["songMemorial-" . $i + 6] ?? $currentPlaylistSong->SongMemorial;
                            $formInput['SongXD'] = $_POST["songXD-" . $i + 7] ?? $currentPlaylistSong->SongXD;
                            $formInput['SongNotRap'] = $_POST["songNotRap-" . $i + 8] ?? $currentPlaylistSong->SongNotRap;
                            $formInput['SongDiscomfort'] = $_POST["songDiscomfort-" . $i + 9] ?? $currentPlaylistSong->SongDiscomfort;
                            $formInput['SongDepA'] = $_POST["songDepA-" . $i + 26] ?? $currentPlaylistSong->SongDepA;
                            $formInput['SongTop'] = $_POST["songTop-" . $i + 10] ?? $currentPlaylistSong->SongTop;
                            $formInput['SongNoGrade'] = $_POST["songNoGrade-" . $i + 11] ?? $currentPlaylistSong->SongNoGrade;
                            $formInput['SongUber'] = $_POST["songUber-" . $i + 12] ?? $currentPlaylistSong->SongUber;
                            $formInput['SongBelow'] = $_POST["songBelow-" . $i + 13] ?? $currentPlaylistSong->SongBelow;
                            $formInput['SongBelTen'] = $_POST["songBelTen-" . $i + 14] ?? $currentPlaylistSong->SongBelTen;
                            $formInput['SongBelNine'] = $_POST["songBelNine-" . $i + 15] ?? $currentPlaylistSong->SongBelNine;
                            $formInput['SongBelEight'] = $_POST["songBelEight-" . $i + 16] ?? $currentPlaylistSong->SongBelEight;
                            $formInput['SongBelFour'] = $_POST["songBelFour-" . $i + 17] ?? $currentPlaylistSong->SongBelFour;
                            $formInput['SongDuoTen'] = $_POST["songDuoTen-" . $i + 18] ?? $currentPlaylistSong->SongDuoTen;
                            $formInput['SongVeto'] = $_POST["songVeto-" . $i + 19] ?? $currentPlaylistSong->SongVeto;
                            $formInput['SongBelHalfSeven'] = $_POST["SongBelHalfSeven-" . $i + 23] ?? $currentPlaylistSong->SongBelHalfSeven;
                            $formInput['SongBelHalfEight'] = $_POST["SongBelHalfEight-" . $i + 24] ?? $currentPlaylistSong->SongBelHalfEight;
                            $formInput['SongBelHalfNine'] = $_POST["SongBelHalfNine-" . $i + 25] ?? $currentPlaylistSong->SongBelHalfNine;

                            //Move and copy song select boxes
                            $formInput['newPlaylistId'] = $_POST["nwPlistId-" . $i + 3];
                            $formInput['copyToPlaylist'] = $_POST["copyPlistId-" . $i + 20];

                            //Song comment textarea
                            $formInput['SongComment'] = $_POST["songComment-" . $i + 22] ?? $currentPlaylistSong->SongComment;

                            //Fatal Error - if the song was not fetched, note this and continue to the next song.
                            if ($currentPlaylistSong === false) {
                                $resultMessage .= "<br><br>\tNie znaleziono utworu o ID " . $formInput['songId'] . "<br><br>";
                                continue;
                            }

                            //Fetch song properties that need updating by comparing the form input data with the current song data
                            $elementsToUpdate = $this->flagSongDataToUpdate($currentPlaylistSong, $formInput);

                            //Update song grades
                            $createUpdateLog = false;
                            $adamGradeUpdated = in_array("SongGradeAdam", $elementsToUpdate);
                            $churchieGradeUpdated = in_array("SongGradeChurchie", $elementsToUpdate);
                            $ownerGradeUpdated = in_array("SongGradeOwner", $elementsToUpdate);
                            $commentUpdated = in_array("SongComment", $elementsToUpdate);
                            if ($adamGradeUpdated || $churchieGradeUpdated || $ownerGradeUpdated) {
                                $newAdamRating = str_replace(',', '.', $formInput['SongGradeAdam']);
                                $newChurchieRating = str_replace(',', '.', $formInput['SongGradeChurchie']);
                                $newOwnerRating = str_replace(',', '.', $formInput['SongGradeOwner']);

                                //Ensure the ratings are valid decimal numbers (full or .5), are in the range "1-15", and are separated with dots and not commas
                                if (strlen($newAdamRating) > 0 && strlen($newChurchieRating) > 0 && strlen($newOwnerRating) > 0
                                    && is_numeric($newAdamRating) && is_numeric($newChurchieRating) && is_numeric($newOwnerRating)
                                    && $this->UtilityModel->InRange($newAdamRating, 0, 15) && $this->UtilityModel->InRange($newChurchieRating, 0, 15) && $this->UtilityModel->InRange($newOwnerRating, 0, 15)
                                    && fmod($newAdamRating, 0.5) == 0 && fmod($newChurchieRating, 0.5) == 0 && fmod($newOwnerRating, 0.5) == 0)
                                {
                                    $scoresSaved = $this->PlaylistSongModel->updatePlaylistSongScores($currentPlaylistSong->id, $newAdamRating, $newChurchieRating, $newOwnerRating);
                                    if (!$scoresSaved) {
                                        //Fatal Error - if grades were not saved, note this and continue to the next song.
                                        $resultMessage .= "<br><br>\tNie udało się zapisać ocen dla utworu " . $currentSong->SongTitle . "<br><br>";
                                        continue;
                                    }

                                    if ($adamGradeUpdated)
                                        $localResultMessage .= "\tOcena Adama: " . $currentPlaylistSong->SongGradeAdam . " -> " . $newAdamRating . "<br>";
                                    if ($churchieGradeUpdated)
                                        $localResultMessage .= "\tOcena Kościelnego: " . $currentPlaylistSong->SongGradeChurchie . " -> " . $newChurchieRating . "<br>";
                                    if ($ownerGradeUpdated)
                                        $localResultMessage .= "\tOcena Właściciela: " . $currentPlaylistSong->SongGradeOwner . " -> " . $newOwnerRating;

                                    $createUpdateLog = true;
                                }
                            }

                            //Update song comment
                            if ($commentUpdated) {
                                $commentSaved = $this->PlaylistSongModel->updateSongComment($currentPlaylistSong->id, $formInput['SongComment']);
                                if (!$commentSaved) {
                                    //Fatal Error - if comment was not saved, note this and continue to the next song.
                                    $resultMessage .= "<br><br>\tNie udało się zapisać komentarza do utworu " . $currentSong->SongTitle . "<br><br>";
                                    continue;
                                }
                                else {
                                    $localResultMessage .= ($localResultMessage == "" ? "\t" : "<br>\t");
                                    $localResultMessage .= "Komentarz: " . $currentPlaylistSong->SongComment . " -> " . $formInput['SongComment'];
                                    $createUpdateLog = true;
                                }
                            }

                            //Update song checkbox properties (buttons)
                            foreach ($elementsToUpdate as $prop) {
                                if (!in_array($prop, ["SongGradeAdam", "SongGradeChurchie", "SongGradeOwner", "SongComment"])) {
                                    $propertyDisplayName = $this->getPropertyDisplayName($prop);
                                    $updateSuccess = $this->PlaylistSongModel->updateSongCheckboxProperty($currentPlaylistSong->id, $prop, $formInput[$prop]);
                                    $localResultMessage .= ($localResultMessage == "" ? "\t" : "<br>\t");
                                    if ($updateSuccess)
                                        $localResultMessage .= ($formInput[$prop] ? "Zaznaczono " : "Odznaczono ") . $propertyDisplayName;
                                    else
                                        $localResultMessage .= "Nie udało się zmienić wartości przycisku " . $propertyDisplayName;
                                    $createUpdateLog = true;
                                }
                            }

                            //Create a song update log if any changes were made
                            if ($createUpdateLog)
                                $this->LogModel->createLog('playlist_song', $currentPlaylistSong->id, "Zapisano oceny nuty z " . ($playlistId === "search" ? "wyszukiwarki" : "playlisty"));

                            //Copy songs to local playlists
                            $newPlaylistSongId = 0;
                            if ($formInput['copyToPlaylist']) {
                                $newPlaylistSongId = $this->PlaylistSongModel->copyToAnotherPlaylist($currentPlaylistSong->id, $formInput['copyToPlaylist']);
                                if ($newPlaylistSongId) {
                                    $sourceName = $playlistId === "search" ? "wyszukiwarki" : "playlisty " . $playlist->ListName;
                                    $targetName = $this->PlaylistModel->getPlaylistNameById($formInput['copyToPlaylist']);
                                    $localResultMessage .= ($localResultMessage == "" ? "\t" : "<br>\t");
                                    $localResultMessage .= "Skopiowano do: ".$targetName;
                                    $this->LogModel->createLog("playlist_song", $newPlaylistSongId, "Nuta skopiowana z ".$sourceName." do ".$targetName);
                                }
                                else {
                                    $resultMessage .= "<br><br>\tNie udało się skopiować utworu ".$currentSong->SongTitle.", przeskoczono do następnego.<br><br>";
                                    continue;
                                }
                            }

                            //Move songs between integrated playlists
                            $moveRequired = $formInput['newPlaylistId'] != $playlistId && $formInput['newPlaylistId'] != 0;
                            if ($moveRequired) {
                                $data['displayErrorMessage'] = $this->InsertSongService->moveSongBetweenIntegratedPlaylists($playlist, $currentPlaylistSong, $formInput['newPlaylistId'], $localResultMessage);
                            }

                            //Copy songs to integrated playlists
                            $copyRequired = $formInput['copyToPlaylist'] != $playlistId && $formInput['copyToPlaylist'] != 0;
                            $copyToIntegratedRequired = $copyRequired && $this->PlaylistModel->getPlaylistIntegratedById($formInput['copyToPlaylist']);
                            if ($copyToIntegratedRequired && $newPlaylistSongId) {
                                $data['displayErrorMessage'] = ($data['displayErrorMessage'] ?? "")."<br>".$this->InsertSongService->copySongToIntegratedPlaylist($currentPlaylistSong, $formInput['copyToPlaylist'], $newPlaylistSongId, $localResultMessage);
                            }

                            //Save the result message and pass it to the report
                            $finalResultMessage = $localResultMessage != "" ? ("<br><br>Utwór ".$currentSong->SongTitle.":<br><br>".$localResultMessage) : "";
                            $resultMessage .= $finalResultMessage;
                        }
                        else redirect('logout');
                    }
                }
                //Finalise the result message
                $data['resultMessage'] = $resultMessage . "</pre>";

                //Submit a report
                $newReportId = $this->LogModel->submitReport(htmlspecialchars($data['resultMessage']));

                //Create a log
                $where = $playlistId === "search" ? "z wyszukiwarki" : "z playlisty";
                $reportSuccessful = $newReportId ? " i dołączono raport." : ", nie udało się zapisać raportu.";
                $logMessage = "Zapisano oceny ".$where.$reportSuccessful;
                if (is_numeric($playlistId))
                    $this->LogModel->createLog('playlist', $playlistId, $logMessage, $newReportId);

                $this->load->view('templates/main', $data);
            }
            else redirect('logout');
        }
        else redirect('logout');
    }

    /**
     * Updates the playlist with new songs added to it on YouTube.
     *
     * @return void
     */
    public function downloadSongs(): void
    {
        $listId = isset($_GET['playlistId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['playlistId'])) : 0;
        $listId = is_numeric($listId) ? $listId : 0;
        $data = array(
            'body' => 'downloadPlaylistSongs',
            'title' => 'Aktualizacja playlisty!',
            'listId' => $listId,
            'displayErrorMessage' => ''
        );

        //Check if the user is logged in and has the required permissions
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        $userAuthorised = $userAuthenticated && $this->PlaylistModel->getListOwnerById($data['listId']) == $_SESSION['userId'];
        if ($userAuthorised) {
            //Check if the playlist has a YouTube link.
            $playlistUrl = $this->PlaylistModel->getListUrlById($data['listId']);
            if (!empty($playlistUrl)) {
                //Refresh the playlist - if everything went well, the message will be empty
                $data['displayErrorMessage'] = $this->RefreshPlaylistService->refreshPlaylist($data['listId']);
            }
            else
                $data['displayErrorMessage'] = "Nie znaleziono linku do tej playlisty na YT!";
        }
        else redirect('logout');

        $this->load->view('templates/main', $data);
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
            $userAuthorised = $userAuthenticated && $this->PlaylistModel->getListOwnerById($playlistSong->listId) == $_SESSION['userId'];
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
            else redirect('logout');
        }
        else redirect('logout');
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
            $userAuthorised = $userAuthenticated && $this->PlaylistModel->getListOwnerById($playlistSong->listId) == $_SESSION['userId'];
            if ($userAuthorised) {
                $data = array(
                    'body' => 'song/delSong',
                    'title' => 'Uber Rapsy | Usuń piosenkę z playlisty',
                    'playlist' => $this->PlaylistModel->fetchPlaylistById($playlistSong->listId),
                    'redirectSource' => $this->input->get('src'),
                    'song' => $this->SongModel->getSong($playlistSong->songId),
                    'playlistSong' => $playlistSong
                );

                //Delete the song if the form was submitted
                $delSong = $this->input->get('delete');
                if ($delSong) {
                    $this->LogModel->createLog('playlist_song', $playlistSongId, "Permanentnie usunięto nutę z playlisty.");
                    $this->LogModel->createLog('playlist', $data['playlistSong']->listId, "Permanentnie usunięto nutę " . $data['song']->SongTitle . " z playlisty.");
                    $this->PlaylistSongModel->deletePlaylistSong($playlistSongId);
                    if ($data['redirectSource'] == 'pd')
                        redirect('playlist/details?listId='.$data['playlistSong']->listId.'&src=pd');
                    else redirect('playlist/details?listId='.$data['playlistSong']->listId.'&src=mp');
                }

                $this->load->view('templates/main', $data);
            }
            else redirect('logout');
        }
        else redirect('logout');
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
     * Based on the reviewers' grades computes the average song score.
     *
     * @param $song object song to calculate the average for
     * @return float|int the average calculated
     */
    public function calculateAverage(object $song): float|int
    {
        $grades = [
            $song->SongGradeAdam,
            $song->SongGradeChurchie,
            $song->SongGradeOwner
        ];

        //Filter out zero values
        $nonZeroGrades = array_filter($grades, function ($grade) {
            return $grade > 0;
        });

        //Calculate the average if there are non-zero grades, otherwise return 0
        return count($nonZeroGrades) > 0 ? round(array_sum($nonZeroGrades) / count($nonZeroGrades), 2) : 0;
    }

    /**
     * Formats playlist song grades and computes the average.
     *
     * @param $song object playlist song to set the grades for
     * @return void the updated playlist song object
     */
    public function setSongGrades(object $song): void
    {
        if (is_numeric($song->SongGradeAdam)) $song->SongGradeAdam = $this->UtilityModel->trimTrailingZeroes($song->SongGradeAdam);
        if (is_numeric($song->SongGradeChurchie)) $song->SongGradeChurchie = $this->UtilityModel->trimTrailingZeroes($song->SongGradeChurchie);
        if (is_numeric($song->SongGradeOwner)) $song->SongGradeOwner = $this->UtilityModel->trimTrailingZeroes($song->SongGradeOwner);
        $song->Average = $this->calculateAverage($song);
    }
}
