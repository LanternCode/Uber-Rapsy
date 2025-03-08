<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(!isset($_SESSION)){
    session_start();
}

/**
 * Controller responsible for handling song-only views
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 */
class Song extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('SongModel');
        $this->load->model('PlaylistModel');
    }

    /**
     * Handles song detailed review screen.
     *
     * @return void
     */
    public function reviewSong()
    {
        //Validate the submitted song id
        $data = [];
        $songId = isset($_GET['id']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['id'])) : NULL;
        $data['song'] = is_numeric($songId) ? $this->SongModel->GetSongById($songId) : false;
        if ($data['song'] !== false) {
            //Check if the user is logged in and has the required permissions
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $listOwnerId = $this->PlaylistModel->GetListOwnerById($data['song']->ListId);
            $data['owner'] = $listOwnerId == $_SESSION['userId'];
            $data['playlist'] = $this->PlaylistModel->fetchPlaylistById($data['song']->ListId);

            $userAuthorised = $userAuthenticated && $data['owner'];
            $userAuthorised = $userAuthorised || $data['playlist']->ListPublic;
            if ($userAuthorised) {
                //Check if there is an existing review
                $data['body'] = "song/revSong";
                $reviewExists = (array) $this->SongModel->GetSongReview($songId, $listOwnerId);
                $data['existingReview'] = count($reviewExists) == 1 ? false : $reviewExists;
                $data['errorMessage'] = "";

                //Check if the post review form was submitted and the user is the playlist owner
                if (isset($_POST['reviewMusic']) && $data['owner']) {
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
                    foreach($review as $key => $revPiece) {
                        $maxAllowed = ($key == "reviewText" || $key == "reviewMusic") ? 20 : (($key == "reviewUber" || $key == "reviewPartner") ? 15 : (($key == "reviewImpact" || $key == "reviewRh") ? 5 : 10));
                        $optName = ($key == "reviewText" ? "Tekst" : ($key == "reviewMusic" ? "Muzyka" : (($key == "reviewUber" ? "Ocena Uber"
                            : (($key == "reviewPartner" ? "Ocena Partnera" : (($key == "reviewImpact" ? "Popularność" : (($key == "reviewRh" ? "Słuchalność"
                                : (($key == "reviewComp" ? "Kompozycja" : "Refleksyjność"))))))))))));

                        if (!is_numeric($revPiece))
                            $data['errorMessage'] .= "Podano niepoprawną wartość dla " . $optName . "!<br>";
                        else if ($revPiece < 1)
                            $data['errorMessage'] .= "Podano niepoprawną wartość minimalną dla " . $optName . ". Ocena musi być większa lub równa 1!<br>";
                        else if ($revPiece > $maxAllowed)
                            $data['errorMessage'] .= "Podano niepoprawną wartość maksymalną dla " . $optName . ". Ocena musi być mniejsza lub równa " . $maxAllowed . "!<br>";
                        else if(fmod($revPiece, 0.5) != 0)
                            $data['errorMessage'] .= "Podano niepoprawną ocenę dla " . $optName . ". Ocena musi być pełną liczbą lub zakończoną połówką, np. 5.5!<br>";
                    }

                    //Get Non-numeric date
                    $review['reviewDate'] = isset($_POST['reviewDate']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['reviewDate'])) : NULL;
                    $review['reviewRev'] = isset($_POST['reviewRev']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['reviewRev'])) : NULL;
                    $review['reviewSongId'] = $songId;
                    $review['reviewUserId'] = $_SESSION['userId'];

                    //Check that a correct date was inserted
                    if (!is_null($review['reviewDate']) && strlen($review['reviewDate']) != 10) {
                        $data['errorMessage'] .= "Podano niepoprawną datę.<br>";
                    }

                    //Only submit/update the review if there are no errors
                    if ($data['errorMessage'] == "") {
                        //Format the date to match the DB formatting
                        $review['reviewDate'] = str_replace('/', '-', $review['reviewDate']);
                        $review['reviewDate'] = date("Y-m-d", strtotime($review['reviewDate']));

                        //If the review already exists, replace it, else, insert it
                        if($data['existingReview'])
                        {
                            $review['reviewId'] = $data['existingReview']['reviewId'];
                            $this->SongModel->UpdateReview($review);
                        }
                        else $this->SongModel->InsertSongReview($review);
                        $data['successMessage'] = 1;

                        $this->LogModel->createLog('song', $songId, "Zrecenzowano utwór");
                    }

                    //Update the review data with the newly inserted data for user convenience
                    $data['existingReview'] = $review;
                }
                else if (isset($_POST['reviewMusic']) && !$owner)
                    redirect('logout');

                $this->load->view('templates/main', $data);
            }
            else redirect('logout');
        }
        else redirect('logout');
    }

    /**
     * Allows the user to see the logs of a song
     *
     * @return void
     */
    public function showLog()
    {
        //Validate the submitted song id
        $data = [];
        $songId = isset($_GET['songId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['songId'])) : 0;
        $data['song'] = is_numeric($songId) ? $this->SongModel->GetSongById($songId) : false;
        if ($data['song'] !== false) {
            //Check if the user is logged in and has the required permissions
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = $userAuthenticated && $this->PlaylistModel->GetListOwnerById($data['song']->ListId) == $_SESSION['userId'];
            if ($userAuthorised) {
                $data['body']  = 'song/showLog';
                $data['title'] = "Uber Rapsy | Historia nuty";
                $data['songLog'] = $this->LogModel->GetSongLog($songId);
                $data['redirectSource'] = isset($_GET['src']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['src'])) : 0;

                $this->load->view('templates/main', $data);
            }
            else redirect('logout');
        }
        else redirect('logout');
    }

    /**
     * Allows the user to hide and show a song in a playlist.
     *
     * @return void
     */
    public function updateSongVisibility()
    {
        //Validate the submitted song id
        $songId = isset($_GET['songId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['songId'])) : 0;
        $song = is_numeric($songId) ? $this->SongModel->GetSongById($songId) : false;
        if ($song !== false) {
            //Check if the user is logged in and has the required permissions
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = $userAuthenticated && $this->PlaylistModel->GetListOwnerById($song->ListId) == $_SESSION['userId'];
            if ($userAuthorised) {
                //Update song visibility
                $currentVisibility = $song->SongVisible;
                $newVisibility = $currentVisibility == 1 ? 0 : 1;
                $this->SongModel->UpdateSongVisibility($songId, $newVisibility);
                $this->LogModel->createLog('song', $songId, ($newVisibility ? "Upubliczniono" : "Ukryto") . " nutę na playliście");

                //Return to the playlist details view
                $redirectSource = isset($_GET['src']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['src'])) : 0;
                if ($redirectSource == 'pd')
                    redirect('playlist/details?listId='.$song->ListId.'&src=pd');
                else redirect('playlist/details?listId='.$song->ListId.'&src=mp');
            }
            else redirect('logout');
        }
        else redirect('logout');
    }

    public function songsToplist()
    {
        $data = array(
            'body' => 'song/toplist',
            'title' => 'Uber Rapsy | Toplista Nut',
            'songs' => $this->SongModel->fetchSongsForToplist()
        );

        $this->load->view('templates/toplist', $data);
    }

    public function saveGradesFromToplist()
    {
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        if ($userAuthenticated) {
            $queryData['userId'] = $_SESSION['userId'];
            $queryData['songGrade'] = $this->input->post('songGrade');
            $queryData['songId'] = $this->input->post('songId');

            $songUnrated = !$this->SongModel->checkSongRatingExists($queryData);;
            if($songUnrated)
                $this->SongModel->addSongRating($queryData);
            else
                $this->SongModel->updateSongRating($queryData);

            redirect('songsToplist');
        }
        else redirect('logout');
    }
}