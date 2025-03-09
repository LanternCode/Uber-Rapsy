<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!isset($_SESSION)) {
    session_start();
}

/**
 * Controller responsible for handling toplist-related views
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
 * @property CI_Input $input
 */
class Toplist extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('SongModel');
        $this->load->model('SecurityModel');
        $this->load->model('PlaylistModel');
        $this->load->model('UtilityModel');
    }

    /**
     * Opens the song toplist.
     *
     * @return void
     */
    public function songsToplist(): void
    {
        $data = array(
            'body' => 'song/toplist',
            'title' => 'Uber Rapsy | Toplista Nut',
            'songs' => $this->SongModel->fetchSongsForToplist(),
            'ratings' => $this->SongModel->fetchSongRating(7998, $_SESSION['userId']),
            'averages' => $this->UtilityModel->TrimTrailingZeroes($this->SongModel->fetchSongAverage(7998))
        );

        $this->load->view('templates/toplist', $data);
    }

    /**
     * Saves user grades from toplists.
     *
     * @return void
     */
    public function saveGradesFromToplist(): void
    {
        $userAuthenticated = $this->SecurityModel->authenticateUser();
        if ($userAuthenticated) {
            //Fetch the new ratings
            $queryData['userId'] = $_SESSION['userId'];
            $queryData['songGrade'] = filter_var($this->input->post('songGrade'), FILTER_VALIDATE_FLOAT);
            $queryData['songId'] = filter_var($this->input->post('songId'), FILTER_VALIDATE_INT);

            //Check if the user already rated the song
            if($queryData['songGrade'] && $queryData['songId']) {
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

}