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
 * @property CI_DB_mysqli_driver $db
 * @property CI_Session $session
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
            'body' => 'toplist/frontpage',
            'title' => 'Nasze toplisty | Uber Rapsy',
            'songs' => $this->SongModel->fetchTopRapparHits()
        );

        foreach ($data['songs'] as $song) {
            $song->myRating = isset($_SESSION['userId']) ? $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongRating($song->SongId, $_SESSION['userId'])) : 0;
            $song->communityAverage = $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongAverage($song->SongId));
            $song->awards = $this->SongModel->fetchSongAwards($song->SongId);
        }

        $this->load->view('templates/toplist', $data);
    }

    /**
     * Opens the individual song's page
     *
     * @return void
     */
    public function songPage(): void
    {
        $songId = filter_var($this->input->get('songId'), FILTER_VALIDATE_INT);
        if ($songId) {
            $data = array(
                'body' => 'song/songPage',
                'title' => 'Uber Rapsy | Oceń nutę',
                'song' => $this->SongModel->getSong($songId),
                'myRating' => isset($_SESSION['userId']) ? $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongRating($songId, $_SESSION['userId'])) : 0,
                'communityAverage' => $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongAverage($songId)),
                'songAwards' => $this->SongModel->fetchSongAwards($songId)
            );
            $data['song']->SongGradeAdam = $this->UtilityModel->trimTrailingZeroes($data['song']->SongGradeAdam ?? 0);
            $data['song']->SongGradeChurchie = $this->UtilityModel->trimTrailingZeroes($data['song']->SongGradeChurchie ?? 0);

            $this->load->view('templates/toplist', $data);
        }
        else redirect('logout');
    }

    /**
     * Saves user grades from toplists.
     *
     * @return void
     */
    public function rateSong(): void
    {
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        if ($userAuthenticated) {
            //Fetch the new ratings
            $queryData['userId'] = $_SESSION['userId'];
            $queryData['songGrade'] = filter_var($this->input->post('songGrade'), FILTER_VALIDATE_FLOAT);
            $queryData['songId'] = filter_var($this->input->post('songId'), FILTER_VALIDATE_INT);

            //Check if the user already rated the song
            if ($queryData['songGrade'] && $queryData['songId']) {
                $songUnrated = !$this->SongModel->checkSongRatingExists($queryData['songId'], $queryData['userId']);
                if ($songUnrated)
                    $this->SongModel->addSongRating($queryData);
                else
                    $this->SongModel->updateSongRating($queryData);
            }
            redirect('songsToplist');
        }
        else redirect('logout');
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
            'searchQuery' => trim($this->input->get('searchQuery') ?? '')
        );

        //Fetch songs filtered by a valid search query
        if (strlen($data['searchQuery']) >= 1) {
            //Fetch per-song properties if 300 or less songs were returned
            $data['songs'] = $this->SongModel->searchSongs($data['searchQuery']);
            if (count($data['songs']) <= 300) {
                foreach ($data['songs'] as $song) {
                    $song->myGrade = isset($_SESSION['userId']) ? $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongRating($song->SongId, $_SESSION['userId'])) : 0;
                    $song->communityAverage = $this->UtilityModel->trimTrailingZeroes($this->SongModel->fetchSongAverage($song->SongId));
                    $song->awards = $this->SongModel->fetchSongAwards($song->SongId);
                }
            }
        }

        $this->load->view('templates/toplist', $data);
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
            }
            else {
                //If no items were found, show an error
                $data['error'] = '<h3>Nie znaleziono żadnych utworów. Upewnij się, że playlista bądź wskazany utwór są publiczne, i że playlista zawiera przynajmniej jeden publiczny utwór.</h3><br>';
            }
        }

        $this->load->view('templates/toplist', $data);
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
    public function confirmSongImport()
    {
        $i = 0;
        $added = 0;
        $data['report'] = "";

        //Load the previously fetched songs array
        $songItems = $this->session->tempdata('playlistItems');
        if (count($songItems) > 0) {
            foreach ($songItems as $song) {
                //Import each song if it does not exist yet
                $songChannelName = $this->input->post("songChannelName-".$i) != $song['songChannelName'] ? $this->input->post("songChannelName-".$i) : $song['songChannelName'];
                $existingSongId = $this->SongModel->songExists($song['externalSongId'], $song['songTitle'], $songChannelName);
                if ($existingSongId == 0) {
                    $songId = $this->SongModel->insertSong($song['externalSongId'], $song['songThumbnailLink'], $song['songTitle'], $songChannelName, $song['songPublishedAt']);
                    $data['report'] .= "<h4>Utwór ".$song['songTitle']." został dodany do bazy danych Rappar!</h4><br>";
                    $added++;
                }
                else
                    $data['report'] .= "<h4>Utwór ".$song['songTitle']." już istnieje w bazie danych Rappar!</h4><br>";

                $i++;
            }
        }

        //Complete the report
        $word = $added === 1 ? 'utwór' : ($added === 2 || $added === 3 || $added === 4 ? 'utwory' : 'utworów');
        $data['report'] .= "<h2>Łącznie dodano ".$added." ".$word." do bazy danych RAPPAR!</h2>";
        $this->session->unset_tempdata('playlistItems');

        $data['body'] = 'song/importSongsResult';
        $this->load->view('templates/toplist', $data);
    }

    /**
     * Handles detailed song reviews.
     *
     * @return void
     */
    public function reviewSong()
    {
        //Validate the submitted song id
        $data = [];
        $songId = filter_var($this->input->get('id'), FILTER_VALIDATE_INT);
        $data['song'] = is_numeric($songId) ? $this->SongModel->getSong($songId) : false;
        if ($data['song'] !== false) {
            //Check if the user is logged in and has the required permissions
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = $userAuthenticated && !$data['song']->SongDeleted;
            if ($userAuthorised) {
                //Check if there is an existing review
                $data['body'] = "song/reviewSong";
                $reviewExists = (array) $this->SongModel->getSongReview($songId, $_SESSION['userId']);
                $data['existingReview'] = count($reviewExists) == 1 ? false : $reviewExists;
                $data['errorMessage'] = "";

                //Check if the post review form was submitted
                if (isset($_POST['reviewMusic'])) {
                    //Get the numeric data
                    $review['reviewText'] = isset($_POST['reviewText']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['reviewText'])) : 0;
                    $review['reviewMusic'] = trim(mysqli_real_escape_string($this->db->conn_id, $_POST['reviewMusic']));
                    $review['reviewImpact'] = isset($_POST['reviewImpact']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['reviewImpact'])) : 0;
                    $review['reviewRh'] = isset($_POST['reviewRh']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['reviewRh'])) : 0;
                    $review['reviewComp'] = isset($_POST['reviewComp']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['reviewComp'])) : 0;
                    $review['reviewReflection'] = isset($_POST['reviewReflection']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['reviewReflection'])) : 0;
                    $review['reviewUber'] = isset($_POST['reviewUber']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['reviewUber'])) : 0;
                    $review['reviewPartner'] = isset($_POST['reviewPartner']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['reviewPartner'])) : 0;
                    $data['errorMessage'] = "";
                    $data['successMessage'] = 0;

                    //Validate the numeric data
                    foreach ($review as $key => $revPiece) {
                        $maxAllowed = ($key == "reviewText" || $key == "reviewMusic") ? 20 : (($key == "reviewUber" || $key == "reviewPartner") ? 15 : (($key == "reviewImpact" || $key == "reviewRh") ? 5 : 10));
                        $optName = ($key == "reviewText" ? "Tekst" : ($key == "reviewMusic" ? "Muzyka" : (($key == "reviewUber" ? "Ocena Uber"
                            : (($key == "reviewPartner" ? "Ocena Partnera" : (($key == "reviewImpact" ? "Popularność" : (($key == "reviewRh" ? "Słuchalność"
                                : (($key == "reviewComp" ? "Kompozycja" : "Refleksyjność"))))))))))));

                        if (!is_numeric($revPiece))
                            $data['errorMessage'] .= "Podano niepoprawną wartość dla ".$optName."!<br>";
                        elseif ($revPiece < 1)
                            $data['errorMessage'] .= "Podano niepoprawną wartość minimalną dla ".$optName.". Ocena musi być większa lub równa 1!<br>";
                        elseif ($revPiece > $maxAllowed)
                            $data['errorMessage'] .= "Podano niepoprawną wartość maksymalną dla ".$optName.". Ocena musi być mniejsza lub równa ".$maxAllowed."!<br>";
                        elseif (fmod($revPiece, 0.5) != 0)
                            $data['errorMessage'] .= "Podano niepoprawną ocenę dla ".$optName.". Ocena musi być pełną liczbą lub zakończoną połówką, np. 5.5!<br>";
                    }

                    //Get the non-numeric data
                    $review['reviewDate'] = isset($_POST['reviewDate']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['reviewDate'])) : NULL;
                    $review['reviewRev'] = isset($_POST['reviewRev']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['reviewRev'])) : NULL;
                    $review['reviewSongId'] = $songId;
                    $review['reviewUserId'] = $_SESSION['userId'];

                    //Verify that a correct review date was provided
                    if (!is_null($review['reviewDate']) && strlen($review['reviewDate']) != 10) {
                        $data['errorMessage'] .= "Podano niepoprawną datę.<br>";
                    }

                    //Only submit or update the review if there were no errors in the form
                    if ($data['errorMessage'] == "") {
                        //Format the date to match the DB formatting
                        $review['reviewDate'] = str_replace('/', '-', $review['reviewDate']);
                        $review['reviewDate'] = date("Y-m-d", strtotime($review['reviewDate']));

                        //If the review already exists, replace it
                        $data['successMessage'] = 1;
                        if ($data['existingReview']) {
                            $review['reviewId'] = $data['existingReview']['reviewId'];
                            $this->SongModel->updateSongReview($review);
                        }
                        else
                            $this->SongModel->insertSongReview($review);

                        $this->LogModel->createLog('song', $songId, "Zrecenzowano utwór.");
                    }

                    //Populate the review fields for user convenience
                    $data['existingReview'] = $review;
                }

                $this->load->view('templates/main', $data);
            }
            else redirect('logout');
        }
        else redirect('logout');
    }
}