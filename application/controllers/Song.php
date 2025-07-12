<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!isset($_SESSION))
    session_start();

/**
 * Controller responsible for (non-playlist) songs
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 *
 * @property SongModel $SongModel
 * @property SecurityModel $SecurityModel
 * @property PlaylistModel $PlaylistModel
 * @property UtilityModel $UtilityModel
 * @property LogModel $LogModel
 * @property CI_Input $input
 * @property CI_Output $output
 * @property CI_DB_mysqli_driver $db
 * @property CI_Session $session
 * @property CI_Upload $upload
 * @property HTMLSanitiser $htmlsanitiser
 */
class Song extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('SongModel');
        $this->load->model('SecurityModel');
        $this->load->model('PlaylistModel');
        $this->load->model('UtilityModel');
        $this->load->model('LogModel');
        $this->load->library('htmlsanitiser');
        $this->load->library('FetchSongsService');
        $this->FetchSongsService = new FetchSongsService();
    }

    /**
     * Opens the song toplist.
     *
     * @return void
     */
    public function frontpage(): void
    {
        $data = array(
            'body' => 'song/frontpage',
            'title' => 'Listy popularnych piosenek | Uber Rapsy',
            'songs' => $this->SongModel->fetchTopRapparHits()
        );

        foreach ($data['songs'] as $song) {
            $song->myRating = isset($_SESSION['userId']) ? $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongRating($song->SongId, $_SESSION['userId'])) : 0;
            $song->communityAverage = $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongAverage($song->SongId));
            $song->awards = $this->SongModel->fetchSongAwards($song->SongId);
        }

        $this->load->view('templates/song', $data);
    }

    /**
     * Opens the individual song's page
     *
     * @return void
     */
    public function songPage(): void
    {
        //Validate the provided song ig
        $songId = filter_var($this->input->get('songId'), FILTER_VALIDATE_INT);
        if ($songId === false)
            redirect('logout');

        //Fetch song data
        $data = array(
            'body' => 'song/songPage',
            'title' => 'Uber Rapsy | Strona utworu',
            'song' => $this->SongModel->getSong($songId),
            'myRating' => isset($_SESSION['userId']) ? $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongRating($songId, $_SESSION['userId'])) : 0,
            'communityAverage' => $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongAverage($songId)),
            'songAwards' => $this->SongModel->fetchSongAwards($songId),
            'userAuthenticated' => $this->SecurityModel->authenticateUser(),
            'userId' => $this->SecurityModel->getCurrentUserId()
        );
        $data['song']->SongGradeAdam = $this->UtilityModel->trimTrailingZeroes($data['song']->SongGradeAdam ?? 0);
        $data['song']->SongGradeChurchie = $this->UtilityModel->trimTrailingZeroes($data['song']->SongGradeChurchie ?? 0);

        //Fetch my song review if one exists
        if ($data['userAuthenticated']) {
            $myReviewId = $this->SongModel->checkIfUserReviewedSong($songId, $data['userId']);
            $data['myReview'] = $myReviewId !== false ? $this->SongModel->getSongReview($myReviewId, true) : null;
        }

        //Fetch 10 most recent user reviews excluding my song review
        $data['songReviewCount'] = $this->SongModel->getSongReviewCount($songId, $data['userId'] !== false ? $data['userId'] : 0);
        $data['userReviews'] = $this->SongModel->fetchRecentSongReviews($songId, $data['userId'] !== false ? $data['userId'] : 0);

        $this->load->view('templates/song', $data);
    }

    /**
     * @return void
     */
    public function rateSongAuto(): void
    {
        //Authenticate the user
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        if ($userAuthenticated) {
            //Make sure the song exists and is active
            $queryData['songId'] = filter_var($this->input->post('songId'), FILTER_VALIDATE_INT);
            $song = $queryData['songId'] !== false ? $this->SongModel->getSong($queryData['songId']) : false;
            $songDeleted = $song !== false && $song->SongDeleted;
            if (!$songDeleted) {
                //Fetch the new song grade and make sure the proposed grade is different and valid
                $queryData['userId'] = $this->SecurityModel->getCurrentUserId();
                $queryData['songGrade'] = filter_var($this->input->post('songGrade'), FILTER_VALIDATE_FLOAT);
                $existingRating = $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongRating($queryData['songId'], $queryData['userId']));
                $newRatingValid = $queryData['songGrade'] !== false && $queryData['songGrade'] >= 1 && $queryData['songGrade'] <= 10;
                $approveUpdate = $newRatingValid && $queryData['songGrade'] != $existingRating;
                if ($approveUpdate) {
                    //Update or insert the grade
                    $songUnrated = !$this->SongModel->checkSongRatingExists($queryData['songId'], $queryData['userId']);
                    if ($songUnrated)
                        $this->SongModel->addSongRating($queryData);
                    else
                        $this->SongModel->updateSongRating($queryData);

                    $communityAverage = $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongAverage($queryData['songId']));

                    //Respond with JSON
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            'status' => 'success',
                            'my_rating' => $queryData['songGrade'],
                            'community_average' => $communityAverage
                        ]));
                }
                else $error = true;
            }
            else $error = true;
        }
        else $error = true;

        //Refuse the request if any errors were detected
        if (isset($error) && $error) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'failure',
                ]));
        }
    }

    /**
     * This method filters visible songs to be displayed as search results
     *
     * @return void
     */
    public function songSearch(): void
    {
        $data = array(
            'body' => 'song/songSearch',
            'title' => 'Wyniki Wyszukiwania Nut | Uber Rapsy',
            'songs' => array(),
            'searchQuery' => trim($this->input->get('searchQuery') ?? ''),
            'isReviewer' => $this->SecurityModel->authenticateReviewer()
        );

        //Fetch songs filtered by a valid search query
        if (strlen($data['searchQuery']) >= 1) {
            //Fetch per-song properties if 300 or less songs were returned
            $data['songs'] = $this->SongModel->searchSongs($data['searchQuery'], $data['isReviewer']);
            if (count($data['songs']) <= 300) {
                foreach ($data['songs'] as $song) {
                    $song->myGrade = isset($_SESSION['userId']) ? $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongRating($song->SongId, $_SESSION['userId'])) : 0;
                    $song->communityAverage = $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongAverage($song->SongId));
                    $song->awards = $this->SongModel->fetchSongAwards($song->SongId);
                }
            }
        }

        $this->load->view('templates/song', $data);
    }

    /**
     * First part of the song importing process:
     *  - the user enters a link to a youtube playlist, video, or both
     *
     * Second part of the song importing process:
     *  - videos are fetched and shown to the user for verification
     *  - the user can update the artist's name (as by default, YT uses the uploading channel's name)
     *  - once the user is satisfied, they click the approve button at the end of the list
     *  - the user has 24 hours to make changes and approve the import
     *
     * @return void
     */
    public function importSongs(): void
    {
        $data = array(
            'body' => 'song/importSongs',
            'title' => 'Dodaj nowe utwory | Uber Rapsy',
            'playlistLink' => $this->input->post('playlistLink'),
            'songLink' => $this->input->post('songLink')
        );

        //The form is submitted when a link to a playlist or a song is supplied
        if ($data['playlistLink'] || $data['songLink']) {
            //First process the playlist link
            if ($data['playlistLink']) {
                //Fetch YT playlist items
                $remotePlaylistId = $this->UtilityModel->extractPlaylistIdFromLink($data['playlistLink']);
                $playlistItems = $this->FetchSongsService->fetchPlaylistItemsFromYT($remotePlaylistId);

                //Check whether the items were fetched successfully and extract key data into an array
                if (!isset($playlistItems['code']) && !isset($playlistItems['displayMessage'])) {
                    foreach ($playlistItems as $playlistItemsArray) {
                        foreach ($playlistItemsArray as $playlistItem) {
                            //Check if the playlist item is available - deleted and private videos do not have a thumbnail url
                            if (isset($playlistItem['snippet']['thumbnails']['medium']['url'])) {
                                $songItems[] = array(
                                    'externalSongId' => $playlistItem['snippet']['resourceId']['videoId'],
                                    'songTitle' => $playlistItem['snippet']['title'],
                                    'songChannelName' => str_ends_with($playlistItem['snippet']['videoOwnerChannelTitle'], " - Topic") ? substr($playlistItem['snippet']['videoOwnerChannelTitle'], 0, -strlen(" - Topic")) : $playlistItem['snippet']['videoOwnerChannelTitle'],
                                    'songThumbnailLink' => $playlistItem['snippet']['thumbnails']['medium']['url']
                                );
                            }
                        }
                    }

                    //For each playlist item (if there are any), fetch the corresponding video item for its publishedAt date
                    if (isset($songItems)) {
                        $i = 0;
                        $data['videoItems'] = $this->FetchSongsService->fetchVideoItemsFromYT(array_column($songItems, 'externalSongId'));
                        foreach ($data['videoItems'] as $videoItemsArray) {
                            foreach ($videoItemsArray as $videoItem) {
                                $songItems[$i]['songPublishedAt'] = substr($videoItem['snippet']['publishedAt'], 0, 4);
                                $i++;
                            }
                        }
                    }
                }
            }

            //Next, process the individual video link
            if ($data['songLink']) {
                $remoteVideoId = $this->UtilityModel->extractVideoIdFromLink($data['songLink']);
                $video = $this->FetchSongsService->fetchVideoItemsFromYT([$remoteVideoId]);
                if (isset($video[0][0]['snippet']['thumbnails']['medium']['url'])) {
                    $songItems[] = array(
                        'externalSongId' => $remoteVideoId,
                        'songTitle' => $video[0][0]['snippet']['title'],
                        'songChannelName' => str_ends_with($video[0][0]['snippet']['channelTitle'], " - Topic") ? substr($video[0][0]['snippet']['channelTitle'], 0, -strlen(" - Topic")) : $video[0][0]['snippet']['channelTitle'],
                        'songThumbnailLink' => $video[0][0]['snippet']['thumbnails']['medium']['url'],
                        'songPublishedAt' => substr($video[0][0]['snippet']['publishedAt'], 0, 4)
                    );
                }
            }

            //Check if any songs were imported
            if (isset($songItems)) {
                //Save the songs fetched for 24 hours so the user can make changes
                $data['songItems'] = $songItems;
                $this->session->set_tempdata('playlistItems', ($songItems), 86400);
                //Set a manual verification page for the author to review the contents
                $data['body'] = 'song/confirmSongImport';
            } else {
                //If no items were found, show an error
                $data['error'] = '<h3>Nie znaleziono żadnych utworów. Upewnij się, że playlista bądź wskazany utwór są publiczne, i że playlista zawiera przynajmniej jeden publiczny utwór.</h3><br>';
            }
        }

        $this->load->view('templates/song', $data);
    }

    /**
     * Presents the user with the song importing process results, which include:
     *  - a per-song breakdown of whether the song was imported, or already existed
     *  - the number of imported songs
     *
     * If the user took more than 24 hours to approve the process in the previous step,
     *  it will show that 0 songs were imported.
     *
     * @return void
     */
    public function confirmSongImport(): void
    {
        //Make sure the user is logged in to continue
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        $userId = $this->SecurityModel->getCurrentUserId();
        $userAuthorised = $userAuthenticated && $userId !== false;
        if ($userAuthorised) {
            $i = 0;
            $added = 0;
            $data['report'] = "";

            //Load the previously fetched songs array
            $songItems = $this->session->tempdata('playlistItems');
            if (count($songItems) > 0) {
                foreach ($songItems as $song) {
                    //Import each song if it does not exist yet
                    $songChannelName = $this->input->post("songChannelName-" . $i) != $song['songChannelName'] ? $this->input->post("songChannelName-" . $i) : $song['songChannelName'];
                    $existingSongId = $this->SongModel->songExists($song['externalSongId'], $song['songTitle'], $songChannelName);
                    if ($existingSongId == 0) {
                        $songId = $this->SongModel->insertSong($song['externalSongId'], $userId, $song['songThumbnailLink'], $song['songTitle'], $songChannelName, $song['songPublishedAt']);
                        $data['report'] .= "<h4>Utwór " . $song['songTitle'] . " został dodany do bazy danych Rappar!</h4><br>";
                        $this->LogModel->createLog("song", $songId, "Nuta została importowana do bazy danych RAPPAR.");
                        $added++;
                    }
                    else
                        $data['report'] .= "<h4>Utwór " . $song['songTitle'] . " już istnieje w bazie danych Rappar! Jeśli nie pojawia się w wynikach wyszukiwania, to znaczy, że został usunięty.</h4><br>";

                    $i++;
                }
            }

            //Complete the report
            $word = $added === 1 ? 'utwór' : ($added === 2 || $added === 3 || $added === 4 ? 'utwory' : 'utworów');
            $data['report'] .= "<h2>Łącznie dodano " . $added . " " . $word . " do bazy danych RAPPAR!</h2>";
            $this->session->unset_tempdata('playlistItems');

            //Submit the report and add a log
            if ($added > 0) {
                $repId = $this->LogModel->submitReport($data['report']);
                $this->LogModel->createLog("user", $userId, "Importowano " . $added . " " . $word . " do bazy danych RAPPAR!", $repId);
            }

            $data['body'] = 'song/importSongsResult';
            $data['title'] = 'RAPPAR | Importuj utwór';
            $this->load->view('templates/song', $data);
        }
        else redirect('logout');
    }

    public function manualImport(): void
    {
        //Make sure the user is logged in to continue
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();
        $userId = $this->SecurityModel->getCurrentUserId();
        $userAuthorised = $userAuthenticated && $userId !== false;
        if ($userAuthorised) {
            $data['body'] = 'song/manualImport';
            $data['title'] = 'RAPPAR | Importuj utwór';

            //Proceed to import the song if the form was submitted
            if ($this->input->post()) {
                //Validate the song title, author, release date and thumbnail link information
                $flags = $this->validateSongInput($data);
                $linkProvided = $flags['linkProvided'];
                $uploadRequired = $flags['uploadRequired'];

                //If no errors were found, check whether the song is unique
                if (!isset($data['titleError']) && !isset($data['authorError']) && !isset($data['yearError']) && !isset($data['linkError'])) {
                    $existingSongId = $this->SongModel->manualSongExists($data['songTitle'], $data['songAuthor'], $data['songReleaseYear']);
                    if (!$existingSongId) {
                        //Upload a thumbnail file, use the provided link or use the default thumbnail
                        if ($uploadRequired) {
                            $this->processThumbnailFileUpload($data, 'songThumbnailLink');
                        }
                        elseif (!$linkProvided)
                            $data['songThumbnailLink'] = base_url('thumbnails/default.png');

                        //Insert the song if no thumbnail errors were detected
                        if (!isset($data['thumbnailError'])) {
                            $data['body'] = 'song/manualImportResult';
                            $data['insertedSongId'] = $this->SongModel->insertSong('', $userId, $data['songThumbnailLink'] , $data['songTitle'], $data['songAuthor'], $data['songReleaseYear']);
                            $this->LogModel->createLog("song", $data['insertedSongId'], "Nuta została manualnie importowana do bazy danych RAPPAR.");
                            if ($uploadRequired)
                                $this->LogModel->createLog("song", $data['insertedSongId'], "W trakcie importu wgrano plik z miniaturą, która została zapisana pod tym adresem: ".$data['songThumbnailLink'].".");
                        }
                    }
                    elseif (!$this->SongModel->isSongActive($existingSongId))
                         $data['songError'] = "<p class='errorMessage'>Ten utwór już kiedyś istniał w bazie danych RAPPAR, jednak został z niej usunięty. Jeśli uważasz, że to błąd, skontaktuj się z administracją.</p><br>";
                    else
                        $data['songError'] = "<p class='errorMessage'>Ten utwór już istnieje w bazie danych RAPPAR! Kliknij <a href='".base_url('songPage?songId=' . $existingSongId)."' target='_blank'>tutaj</a> by do niego przejść.</p><br>";

                }
            }

            $this->load->view('templates/song', $data);
        }
        else redirect('logout');
    }

    /**
     * Edit an existing song.
     *
     * @return void
     */
    public function editSong(): void
    {
        //Validate the submitted song id
        $songId = filter_var($this->input->get('songId'), FILTER_VALIDATE_INT);
        $data['song'] = $songId ? $this->SongModel->getSong($songId) : false;
        if ($data['song'] !== false) {
            //Check if the user is logged in and has the required permissions
            $userAuthorised = $this->SecurityModel->authenticateReviewer();
            if ($userAuthorised) {
                $data['body'] = 'song/editSong';
                $data['title'] = 'RAPPAR | Edytuj utwór';

                //Update the song if the form was submitted
                if ($this->input->post()) {
                    //Validate the song title, author, release date and thumbnail link information
                    $flags = $this->validateSongInput($data);
                    $linkProvided = $flags['linkProvided'];
                    $uploadRequired = $flags['uploadRequired'];

                    //If no errors were found proceed to the thumbnail selection
                    if (!isset($data['titleError']) && !isset($data['authorError']) && !isset($data['yearError']) && !isset($data['linkError'])) {
                        $songData['SongTitle'] = $data['songTitle'];
                        $songData['SongChannelName'] = $data['songAuthor'];
                        $songData['SongReleaseYear'] = $data['songReleaseYear'];

                        //Upload a thumbnail file, use the provided link or use the default thumbnail
                        if ($uploadRequired) {
                            $this->processThumbnailFileUpload($data, 'SongThumbnailURL');
                        }
                        elseif ($linkProvided)
                            $songData['SongThumbnailURL'] = $data['songThumbnailLink'];
                        else
                            $songData['SongThumbnailURL'] = base_url('thumbnails/default.png');

                        //Update the song if the thumbnail is valid
                        if (!isset($data['thumbnailError'])) {
                            //Process the update
                            $songData['SongId'] = $data['song']->SongId;
                            $this->SongModel->updateSong($songData);
                            $this->LogModel->createLog("song", $songData['SongId'], "Zaktualizowano dane utworu.");

                            //Delete the previous thumbnail file if one was present
                            $parsedSongUrl = parse_url($data['song']->SongThumbnailURL);
                            if (isset($parsedSongUrl['host']) && $parsedSongUrl['host'] === $_SERVER['HTTP_HOST']) {
                                $filePath = $_SERVER['DOCUMENT_ROOT'].$parsedSongUrl['path'];
                                if (file_exists($filePath)) {
                                    unlink($filePath);
                                    $this->LogModel->createLog("song", $songData['SongId'], "W trakcie aktualizacji usunięto poprzednią miniaturę, która znajdowałą się pod adresem: ".$data['song']->SongThumbnailURL.".");
                                }
                            }

                            //Create a new thumbnail upload log
                            if ($uploadRequired)
                                $this->LogModel->createLog("song", $songData['SongId'], "W trakcie aktualizacji wgrano plik z miniaturą, która została zapisana pod tym adresem: ".$songData['SongThumbnailURL'].".");

                            redirect("songPage?songId=".$songData['SongId']);
                        }
                    }
                    elseif ($uploadRequired)
                        $data['thumbnailError'] = "<p class='errorMessage'>Miniaturka może zostać zapisana dopiero, gdy reszta formularza została wypełniona poprawnie. Popraw błędy w formularzu i wybierz plik jeszcze raz.";
                }

                $this->load->view('templates/song', $data);
            }
            else redirect('logout');
        }
        else redirect('logout');
    }

    /**
     * Add and remove song awards.
     *
     * @return void
     */
    public function manageSongAwards(): void
    {
        //Validate the submitted song id
        $songId = filter_var($this->input->get('songId'), FILTER_VALIDATE_INT);
        $data['song'] = $songId ? $this->SongModel->getSong($songId) : false;
        if ($data['song'] !== false) {
            //Check if the user is logged in and has the required permissions
            $userAuthorised = $this->SecurityModel->authenticateReviewer();
            if ($userAuthorised) {
                $data['body'] = 'song/manageRewards';
                $data['title'] = 'RAPPAR | Zarządzaj nagrodami utworu';
                $data['songAwards'] = $this->SongModel->fetchSongAwards($songId);

                //Add an award if the form was submitted
                if ($this->input->post()) {
                    //Validate the posted award
                    $songAward = $this->input->post('awardName') !== null ? trim($this->input->post('awardName')) : null;
                    if (strlen($songAward) < 2)
                        $data['awardError'] = "<p class='errorMessage'>Nagroda musi mieć przynajmniej dwa znaki długości!</p>";

                    if (!isset($data['awardError'])) {
                        $this->SongModel->insertSongAward($songId, $songAward);
                        $this->LogModel->createLog("song", $songId, "Utworowi przyznano następującą nagrodę: ".$songAward.".");
                        redirect('song/awards?songId='.$songId);
                    }
                }

                //Delete an award if the button next to an existing award was pressed
                if ($awardId = $this->input->get('delAward')) {
                    //Check whether the award belongs to said song and fetch its description
                    $indexedAwards = array_column($data['songAwards'], null, 'id');
                    $awardValue = $indexedAwards[$awardId]['award'] ?? null;
                    if ($awardValue !== null) {
                        $this->SongModel->cancelSongAward($awardId);
                        $this->LogModel->createLog("song", $songId, "Usunięto następującą nagrodę utworu: ".$awardValue.".");
                        redirect('song/awards?songId='.$songId);
                    }
                }

                $this->load->view('templates/song', $data);
            }
            else redirect('logout');
        }
        else redirect('logout');
    }

    /**
     * Hide (privatise) or show (publicise) an existing song.
     *
     * @return void
     */
    public function updateSongVisibility(): void
    {
        //Validate the submitted song id
        $songId = filter_var($this->input->get('songId'), FILTER_VALIDATE_INT);
        $song = $songId ? $this->SongModel->getSong($songId) : false;
        if ($song !== false) {
            //Check if the user is logged in and has the required permissions
            $userAuthorised = $this->SecurityModel->authenticateReviewer();
            $userId = $this->SecurityModel->getCurrentUserId();
            if ($userAuthorised) {
                $data = array(
                    'body' => 'song/updateSongVisibility',
                    'title' => 'Uber Rapsy | Zmień widoczność utworu',
                    'song' => $song,
                    'myRating' => $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongRating($songId, $userId)),
                    'communityAverage' => $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongAverage($songId)),
                    'songAwards' => $this->SongModel->fetchSongAwards($songId),
                    'src' => $this->input->get('src'),
                    'searchQuery' => $this->input->get('query')
                );
                $data['song']->SongGradeAdam = $this->UtilityModel->trimTrailingZeroes($data['song']->SongGradeAdam ?? 0);
                $data['song']->SongGradeChurchie = $this->UtilityModel->trimTrailingZeroes($data['song']->SongGradeChurchie ?? 0);

                //Update song visibility if approved
                if ($this->input->get('switch')) {
                    $currentVisibility = $data['song']->SongVisible;
                    $newVisibility = $currentVisibility == 1 ? 0 : 1;

                    $this->SongModel->updateSongVisibility($songId, $newVisibility);
                    $this->LogModel->createLog('song', $songId, ($newVisibility ? "Upubliczniono" : "Ukryto")." utwór");

                    //Return to the source view
                    if ($data['src'] === 'search' && $data['searchQuery'])
                        redirect('songSearch?searchQuery='.$data['searchQuery']);
                    else
                        redirect('song/edit?songId='.$song->SongId);
                }

                $this->load->view('templates/song', $data);
            }
            else redirect('logout');
        }
        else redirect('logout');
    }

    /**
     * Delete a song.
     * Deleted songs are marked as deleted and cannot be added again or restored.
     *
     * @return void
     */
    public function deleteSong(): void
    {
        //Validate the submitted song id
        $songId = filter_var($this->input->get('songId'), FILTER_VALIDATE_INT);
        $song = $songId ? $this->SongModel->getSong($songId) : false;
        if ($song !== false) {
            //Check if the user is logged in and has the required permissions
            $userAuthorised = $this->SecurityModel->authenticateReviewer();
            $userId = $this->SecurityModel->getCurrentUserId();
            if ($userAuthorised) {
                $data = array(
                    'body' => 'song/deleteSong',
                    'title' => 'Uber Rapsy | Permanentnie usuń utwór',
                    'song' => $song,
                    'myRating' => $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongRating($songId, $userId)),
                    'communityAverage' => $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongAverage($songId)),
                    'songAwards' => $this->SongModel->fetchSongAwards($songId),
                    'src' => $this->input->get('src'),
                    'searchQuery' => $this->input->get('query')
                );
                $data['song']->SongGradeAdam = $this->UtilityModel->trimTrailingZeroes($data['song']->SongGradeAdam ?? 0);
                $data['song']->SongGradeChurchie = $this->UtilityModel->trimTrailingZeroes($data['song']->SongGradeChurchie ?? 0);

                //Delete the song if approved
                if ($this->input->get('confirmDeletion')) {
                    $this->SongModel->deleteSong($songId);
                    $this->LogModel->createLog('song', $songId,"Usunięto utwór z RAPPAR.");

                    //Return to the source view
                    if ($data['src'] === 'search' && $data['searchQuery'])
                        redirect('songSearch?searchQuery='.$data['searchQuery']);
                    else
                        redirect('song/edit?songId='.$song->SongId);
                }

                $this->load->view('templates/song', $data);
            }
            else redirect('logout');
        }
        else redirect('logout');
    }

    /**
     * Open and process the new song review form.
     *
     * @return void
     */
    public function newSongReview(): void
    {
        //Make sure the user is logged in
        $userId = $this->SecurityModel->getCurrentUserId();
        if ($userId === false)
            redirect('logout');

        //Validate the provided song id
        $songId = filter_var($this->input->get('songId'), FILTER_VALIDATE_INT);
        $songActive = $songId !== false && $this->SongModel->isSongActive($songId);
        if (!$songActive)
            redirect('logout');

        //Ensure the user has not already reviewed the song - if so, redirect them to their review
        $uniqueReview = $this->SongModel->checkIfUserReviewedSong($songId, $userId);
        if ($uniqueReview !== false)
            redirect('song/showReview?reviewId='.$uniqueReview);

        //Process the new review form if one was submitted
        if ($this->input->post()) {
            //Get the numeric data
            $formData['reviewText'] = $this->input->post('reviewText') ?? 0;
            $formData['reviewMusic'] = $this->input->post('reviewMusic') ?? 0;
            $formData['reviewImpact'] = $this->input->post('reviewImpact') ?? 0;
            $formData['reviewRh'] = $this->input->post('reviewRh') ?? 0;
            $formData['reviewComp'] = $this->input->post('reviewComp') ?? 0;
            $formData['reviewReflection'] = $this->input->post('reviewReflection') ?? 0;
            $formData['reviewUber'] = $this->input->post('reviewUber') ?? 0;
            $formData['reviewPartner'] = $this->input->post('reviewPartner') ?? 0;

            //Validate the numeric data - each piece must be rated at least 1 and has a maximum grade. Halves are allowed.
            $data['errorMessage'] = "";
            foreach ($formData as $key => $reviewSection) {
                $maxAllowed = ($key == "reviewText" || $key == "reviewMusic") ? 20 : (($key == "reviewUber" || $key == "reviewPartner") ? 15 : (($key == "reviewImpact" || $key == "reviewRh") ? 5 : 10));
                $optName = ($key == "reviewText" ? "Tekst" : ($key == "reviewMusic" ? "Muzyka" : (($key == "reviewUber" ? "Ocena Uber"
                    : (($key == "reviewPartner" ? "Ocena Partnera" : (($key == "reviewImpact" ? "Popularność" : (($key == "reviewRh" ? "Słuchalność"
                        : (($key == "reviewComp" ? "Kompozycja" : "Refleksyjność"))))))))))));

                if (!is_numeric($reviewSection))
                    $data['errorMessage'] .= "Podano niepoprawną wartość dla ".$optName."!<br>";
                elseif ($reviewSection < 1)
                    $data['errorMessage'] .= "Podano niepoprawną wartość minimalną dla ".$optName.". Ocena musi być większa lub równa 1!<br>";
                elseif ($reviewSection > $maxAllowed)
                    $data['errorMessage'] .= "Podano niepoprawną wartość maksymalną dla ".$optName.". Ocena musi być mniejsza lub równa ".$maxAllowed."!<br>";
                elseif (fmod($reviewSection, 0.5) != 0)
                    $data['errorMessage'] .= "Podano niepoprawną ocenę dla ".$optName.". Ocena musi być liczbą pełną lub zakończoną połówką, np. 5.5!<br>";
            }

            //Get the categorical data
            $formData['reviewTitle'] = $this->input->post('reviewTitle');
            $formData['reviewDate'] = $this->input->post('reviewDate');
            $formData['reviewTextContent'] = $this->input->post('reviewTextContent');

            //Ensure the review title is at least 1-character long
            $formData['reviewTitle'] = $this->htmlsanitiser->purify($formData['reviewTitle']);
            if (strlen($formData['reviewTitle']) < 1)
                $data['errorMessage'] .= "Tytuł recenzji musi zawierać przynajmniej 1 znak.<br>";

            //Verify that a correct review date was provided and format the date to match the DB formatting
            $formData['reviewDate'] = $this->validateReviewDate($formData['reviewDate']);
            if (empty($formData['reviewDate']))
                $data['errorMessage'] .= "Podano niepoprawną datę.<br>";

            //Ensure the textual review is at least 120-characters long
            $formData['reviewTextContent'] = $this->htmlsanitiser->purify($formData['reviewTextContent']);
            if (strlen($formData['reviewTextContent']) < 120)
                $data['errorMessage'] .= "Recenzja musi zawierać przynajmniej 120 znaków.<br>";

            //Submit and show the review if no errors were found
            if ($data['errorMessage'] == "") {
                $formData['reviewSongId'] = $songId;
                $formData['reviewUserId'] = $userId;
                $reviewId = $this->SongModel->insertSongReview($formData);
                redirect('song/showReview?reviewId='.$reviewId);
            }

            $data['input'] = $formData;
        }

        $data['song'] = $this->SongModel->getSong($songId);
        $data['body'] = "song/newReview";
        $this->load->view('templates/main', $data);
    }

    /**
     * Display and update a song review.
     *
     * @return void
     */
    public function songReview(): void
    {
        //Validate the provided review id
        $reviewId = filter_var($this->input->get('reviewId'), FILTER_VALIDATE_INT);
        $data['review'] = $reviewId !== false ? $this->SongModel->getSongReview($reviewId) : false;
        if ($data['review'] === false)
            redirect('logout');

        //Check if the current user owns the review
        $currentUserId = $this->SecurityModel->getCurrentUserId();
        $data['isReviewOwner'] = (int) $data['review']->reviewUserId === $currentUserId;

        //Users can only access reviews which are public or written by them
        $userAuthorised = $data['isReviewOwner'] || $data['review']->reviewActive;
        if (!$userAuthorised)
            redirect('logout');

        //Process the review update form if the user is the review author
        if ($this->input->post()) {
            //Get the numeric data
            $formData['reviewText'] = $this->input->post('reviewText');
            $formData['reviewMusic'] = $this->input->post('reviewMusic');
            $formData['reviewImpact'] = $this->input->post('reviewImpact');
            $formData['reviewRh'] = $this->input->post('reviewRh');
            $formData['reviewComp'] = $this->input->post('reviewComp');
            $formData['reviewReflection'] = $this->input->post('reviewReflection');
            $formData['reviewUber'] = $this->input->post('reviewUber');
            $formData['reviewPartner'] = $this->input->post('reviewPartner');

            //Validate the numeric data - each piece must be rated at least 1 and has a maximum grade. Halves are allowed.
            $data['errorMessage'] = "";
            foreach ($formData as $key => $reviewSection) {
                $maxAllowed = ($key == "reviewText" || $key == "reviewMusic") ? 20 : (($key == "reviewUber" || $key == "reviewPartner") ? 15 : (($key == "reviewImpact" || $key == "reviewRh") ? 5 : 10));
                $optName = ($key == "reviewText" ? "Tekst" : ($key == "reviewMusic" ? "Muzyka" : (($key == "reviewUber" ? "Ocena Uber"
                    : (($key == "reviewPartner" ? "Ocena Partnera" : (($key == "reviewImpact" ? "Popularność" : (($key == "reviewRh" ? "Słuchalność"
                        : (($key == "reviewComp" ? "Kompozycja" : "Refleksyjność"))))))))))));

                if (!is_numeric($reviewSection))
                    $data['errorMessage'] .= "Podano niepoprawną wartość dla ".$optName."!<br>";
                elseif ($reviewSection < 1)
                    $data['errorMessage'] .= "Podano niepoprawną wartość minimalną dla ".$optName.". Ocena musi być większa lub równa 1!<br>";
                elseif ($reviewSection > $maxAllowed)
                    $data['errorMessage'] .= "Podano niepoprawną wartość maksymalną dla ".$optName.". Ocena musi być mniejsza lub równa ".$maxAllowed."!<br>";
                elseif (fmod($reviewSection, 0.5) != 0)
                    $data['errorMessage'] .= "Podano niepoprawną ocenę dla ".$optName.". Ocena musi być liczbą pełną lub zakończoną połówką, np. 5.5!<br>";
            }

            //Get the categorical data
            $formData['reviewTitle'] = $this->input->post('reviewTitle');
            $formData['reviewDate'] = $this->input->post('reviewDate');
            $formData['reviewTextContent'] = $this->input->post('reviewTextContent');

            //Ensure the review title is at least 1-character long
            $formData['reviewTitle'] = $this->htmlsanitiser->purify($formData['reviewTitle']);
            if (strlen($formData['reviewTitle']) < 1)
                $data['errorMessage'] .= "Tytuł recenzji musi zawierać przynajmniej 1 znak.<br>";

            //Verify that a correct review date was provided and format the date to match the DB formatting
            $formData['reviewDate'] = $this->validateReviewDate($formData['reviewDate']);
            if (empty($formData['reviewDate']))
                $data['errorMessage'] .= "Podano niepoprawną datę.<br>";

            //Ensure the textual review is at least 120-characters long
            $formData['reviewTextContent'] = $this->htmlsanitiser->purify($formData['reviewTextContent']);
            if (strlen($formData['reviewTextContent']) < 120)
                $data['errorMessage'] .= "Recenzja musi zawierać przynajmniej 120 znaków.<br>";

            //Submit and show the review if no errors were found
            if ($data['errorMessage'] == "") {
                $formData['reviewId'] = $reviewId;
                $formData['reviewSongId'] = $data['review']->reviewSongId;
                $formData['reviewUserId'] = $currentUserId;
                $this->SongModel->updateSongReview($formData);
                redirect('song/showReview?reviewId='.$reviewId);
            }

            $data['input'] = $formData;
        }

        $data['song'] = $this->SongModel->getSong($data['review']->reviewSongId);
        $data['body'] = "song/songReview";
        $this->load->view('templates/main', $data);
    }

    /**
     * Validate the song title, song authors, song release year and the song thumbnail link provided by the user.
     * Check if file upload is necessary.
     *
     * @param array $data the data object passes by reference (to store error messages)
     * @return bool[] return two flags: whether a thumbnail link was provided and whether file upload is required.
     */
    private function validateSongInput(array &$data): array
    {
        //Validate the posted song title
        $data['songTitle'] = $this->input->post('songTitle') !== null ? trim($this->input->post('songTitle')) : null;
        if (!strlen($data['songTitle']) > 0)
            $data['titleError'] = "<p class='errorMessage'>Musisz podać tytuł utworu!</p>";

        //Validate the posted song author
        $data['songAuthor'] = $this->input->post('songAuthor') !== null ? trim($this->input->post('songAuthor')) : null;
        if (!strlen($data['songAuthor']) > 0)
            $data['authorError'] = "<p class='errorMessage'>Musisz podać przynajmniej jednego autora utworu!</p>";

        //Validate the posted song release year
        $data['songReleaseYear'] = $this->input->post('songReleaseYear') !== null ? trim($this->input->post('songReleaseYear')) : null;
        if (strlen($data['songReleaseYear']) != 4)
            $data['yearError'] = "<p class='errorMessage'>Musisz podać rok wydania utworu!</p>";
        elseif ($data['songReleaseYear'] > (int)date("Y") + 30 || $data['songReleaseYear'] < 1800)
            $data['yearError'] = "<p class='errorMessage'>Musisz podać poprawny rok wydania utworu!</p>";

        //Validate the thumbnail link if one was posted
        $linkProvided = false;
        $data['songThumbnailLink'] = $this->input->post('songThumbnailLink') !== null ? trim($this->input->post('songThumbnailLink')) : null;
        if (strlen($data['songThumbnailLink']) > 0) {
            $linkProvided = true;
            if (!filter_var($data['songThumbnailLink'], FILTER_VALIDATE_URL))
                $data['linkError'] = "<p class='errorMessage'>Podano niepoprawny link do miniatury!</p>";
        }

        //Check whether a thumbnail file was posted
        $uploadRequired = false;
        $songThumbnailFile = $_FILES['songThumbnailFile'] ?? null;
        if (isset($songThumbnailFile) && $songThumbnailFile['size'] > 0)
            $uploadRequired = true;

        return [
            'linkProvided' => $linkProvided,
            'uploadRequired' => $uploadRequired
        ];
    }

    /**
     * Validate the thumbnail file provided by the user.
     * If it is correct, upload it and return the link.
     * If it is faulty, provide an error message.
     *
     * @param array $data the data object passed by reference to store the error messages
     * @param string $arrayKeyName importing and editing use a different key
     * @return void
     */
    private function processThumbnailFileUpload(array &$data, string $arrayKeyName): void
    {
        //Specify the upload parameters and initialise the library
        $config['upload_path'] = './thumbnails/';
        $config['allowed_types'] = 'jpg|jpeg|png|gif|webp';
        $config['max_size'] = 10240; //10MB
        $config['max_width'] = 3840;
        $config['max_height'] = 2160;
        $config['file_name'] = time().'_'.bin2hex(random_bytes(4));
        $config['overwrite'] = false;
        $this->load->library('upload', $config);

        //Upload the file
        if (!$this->upload->do_upload('songThumbnailFile')) {
            $data['thumbnailError'] = "<p class='errorMessage'>Nie udało się przesłać miniatury z następujących powodów: <br>";
            $data['thumbnailError'] .= $this->upload->display_errors();
            $data['thumbnailError'] .= "</p>";
        }
        else {
            //Validate the minimum file dimensions
            $file_data = $this->upload->data();
            if ($file_data['image_width'] < 320 || $file_data['image_height'] < 180) {
                unlink($file_data['full_path']);
                $data['thumbnailError'] = "<p class='errorMessage'>Rozdzielczość miniatury nie spełnia minimalnych wymagań.</p>";
            }
            else {
                $data[$arrayKeyName] = base_url('thumbnails/' . $file_data['file_name']);
            }
        }
    }

    /**
     * Validate the date provided by the user during the song review process.
     * Reviews allow dates from 1975 (the widely accepted beginnings of rap) to today.
     *
     * @param $inputDate
     * @return string|null return null if the entered date was incorrect
     */
    private function validateReviewDate($inputDate): string|null
    {
        //Specify recognised date formats
        $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'm/d/Y'];

        //Replace common separators with dashes
        $inputDate = trim(str_replace(['/', '.'], '-', $inputDate));

        //Check if the inserted date matches any of the recognised formats
        foreach ($formats as $format) {
            $d = DateTime::createFromFormat($format, $inputDate);
            if ($d && $d->format($format) === $inputDate) {
                $normalized = $d->format('Y-m-d');

                //Check date bounds
                if ($normalized > date('Y-m-d') || $normalized < '1975-01-01')
                    return null;
                else return $normalized;
            }
        }

        //The entered date did not match any accepted format
        return null;
    }
}