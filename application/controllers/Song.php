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
        $data = [];
        $data['body'] = 'login';
        $userLoggedIn = $_SESSION['userLoggedIn'] ?? false;

        if($userLoggedIn)
        {
            $songId = isset($_GET['id']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['id'])) : NULL;
            $data['body'] = "song/revSong";
            $data['song'] = $this->SongModel->GetSongById($songId);

            //Check if there is an existing review
            $reviewExists = (array) $this->SongModel->GetSongReview($songId, $_SESSION['userId']);
            $data['existingReview'] = count($reviewExists) == 1 ? false : $reviewExists;
            $data['errorMessage'] = "";

            //Check if the post review form was submitted
            if(isset($_POST['reviewMusic']))
            {
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
                foreach($review as $key => $revPiece)
                {
                    $maxAllowed = ($key == "reviewText" || $key == "reviewMusic") ? 20 : (($key == "reviewUber" || $key == "reviewPartner") ? 15 : (($key == "reviewImpact" || $key == "reviewRh") ? 5 : 10));
                    $optName = ($key == "reviewText" ? "Tekst" : ($key == "reviewMusic" ? "Muzyka" : (($key == "reviewUber" ? "Ocena Uber"
                        : (($key == "reviewPartner" ? "Ocena Partnera" : (($key == "reviewImpact" ? "Popularność" : (($key == "reviewRh" ? "Słuchalność"
                            : (($key == "reviewComp" ? "Kompozycja" : "Refleksyjność"))))))))))));

                    if(!is_numeric($revPiece))
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
                if(!is_null($review['reviewDate']) && strlen($review['reviewDate']) != 10)
                {
                    $data['errorMessage'] .= "Podano niepoprawną datę.<br>";
                }

                //Only submit/update the review if there are no errors
                if($data['errorMessage'] == "")
                {
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

                    $this->LogModel->CreateLog('song', $songId, "Zrecenzowano utwór");
                }

                //Update the review data with the newly inserted data for user convenience
                $data['existingReview'] = $review;
            }
        }
        else redirect('logout');

        $this->load->view('templates/main', $data);
    }

    /**
     * Allows the user to see the logs of the song
     *
     * @return void
     */
    public function showLog()
    {
        $userAuthenticated = $this->SecurityModel->authenticateUser();

        if($userAuthenticated)
        {
            $data = [];
            $data['body']  = 'song/showLog';
            $data['title'] = "Uber Rapsy | Historia nuty";
            $data['songId'] = isset($_GET['id']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['id'])) : 0;

            if($data['songId'] && is_numeric($data['songId']))
            {
                $data['song'] = $this->SongModel->GetSongById($data['songId']);
                $data['songLog'] = $this->LogModel->GetSongLog($data['songId']);

                if($data['song'] === false)
                {
                    $data['body']  = 'invalidAction';
                    $data['title'] = "Błąd akcji!";
                    $data['errorMessage'] = "Nie znaleziono nuty o podanym numerze id!";
                }

                $this->LogModel->CreateLog('song', $data['songId'], "Otworzono historię nuty");
            }
            else
            {
                $data['body']  = 'invalidAction';
                $data['title'] = "Błąd akcji!";
                $data['errorMessage'] = "Podano niepoprawny numer id nuty lub nie podano go wcale!";
            }

            $this->load->view( 'templates/main', $data );
        }
        else redirect('logout');
    }

    /**
     * Filters all songs by the title and shows them in a playlist
     *
     * @return void
     */
    public function search()
    {
        $data = [];
        $data['title'] = "Wyniki Wyszukiwania | Uber Rapsy";
        $data['body'] = 'playlistSearch';
        $data['reviewer'] = isset($_SESSION['userRole']) ? ($_SESSION['userRole'] == "reviewer" ? true : false) : false;
        $data['Search'] = isset( $_GET['Search'] ) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_GET['Search'] ) ) : 0;
        $data['songs'] = $this->SongModel->GetSongsFromSearch($data['Search']);
        $data['lists'] = $this->PlaylistModel->GetListsIdsAndNames();
        $data['songPlaylistNames'] = [];
        $data['playlist'] = [];

        foreach($data['songs'] as $i => $song)
        {
            //Fill the playlist name array (1 entry per song)
            $data['songPlaylistNames'][] = $this->PlaylistModel->GetPlaylistNameById($song->ListId);
            //Display values without decimals at the end if the decimals are only 0
            if(is_numeric($song->SongGradeAdam)) $song->SongGradeAdam = $this->TrimTrailingZeroes($song->SongGradeAdam);
            if(is_numeric($song->SongGradeChurchie)) $song->SongGradeChurchie = $this->TrimTrailingZeroes($song->SongGradeChurchie);
            //Get song button information
            $data['playlist'][] = $this->PlaylistModel->FetchPlaylistById($song->ListId);
        }

        $this->load->view( 'templates/customNav', $data );
    }

    /**
     * Trims trailing zeroes from a given number.
     *
     * @param float $nbr number to trim
     * @return float trimmed number
     */
    function TrimTrailingZeroes(float $nbr): float
    {
        return str_contains($nbr, '.') ? rtrim(rtrim($nbr,'0'),'.') : $nbr;
    }
}