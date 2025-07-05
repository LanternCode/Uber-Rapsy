<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!isset($_SESSION))
    session_start();

/**
 * Controller responsible for handling views related to data logging.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 *
 * @property LogModel $LogModel
 * @property SecurityModel $SecurityModel
 * @property PlaylistModel $PlaylistModel
 * @property PlaylistSongModel $PlaylistSongModel
 * @property SongModel $SongModel
 * @property CI_Input $input
 */
class Log extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('SecurityModel');
        $this->load->model('LogModel');
        $this->load->model('PlaylistModel');
        $this->load->model('PlaylistSongModel');
        $this->load->model('SongModel');
    }

    /**
     * This function fetches a report and passes it to be displayed by the user
     *
     * @return void
     */
    public function displayReport(): void
    {
        //Check if the provided report id is valid
        $reportId = filter_var($this->input->get('repId'), FILTER_VALIDATE_INT);
        if ($reportId) {
            //Check if the user is logged in and has the required permissions
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = $userAuthenticated && $this->LogModel->getReportOwnerById($reportId) == $_SESSION['userId'];
            if ($userAuthorised) {
                $data = array(
                    'body' => 'showReport',
                    'title' => 'Uber Rapsy | Widok Raportu',
                    'report' => $this->LogModel->fetchReport($reportId)
                );
                $this->load->view('templates/customNav', $data);
            }
            else redirect('logout');
        }
        else redirect('logout');
    }

    /**
     * Show playlist logs
     *
     * @return void
     */
    public function showPlaylistLog(): void
    {
        $playlistId = filter_var($this->input->get('playlistId'), FILTER_VALIDATE_INT);
        if ($playlistId) {
            //Check if the user is logged in and has the required permissions
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = $userAuthenticated && $this->PlaylistModel->getListOwnerById($playlistId) == $_SESSION['userId'];
            if ($userAuthorised) {
                $data = array(
                    'body' => 'playlist/showLog',
                    'title' => 'Uber Rapsy | Historia playlisty',
                    'playlist' => $this->PlaylistModel->fetchPlaylistById($playlistId),
                    'playlistLog' => $this->LogModel->getPlaylistLog($playlistId),
                    'redirectSource' => $this->input->get('src')
                );

                $this->load->view('templates/main', $data);
            }
            else redirect('logout');
        }
        else redirect('logout');
    }

    /**
     * Show playlist_song logs
     *
     * @return void
     */
    public function showPlaylistSongLog(): void
    {
        //Validate the submitted song id and fetch said song
        $playlistSongId = $this->input->get('songId');
        $data['playlistSong'] = $playlistSongId ? $this->PlaylistSongModel->getPlaylistSong($playlistSongId) : false;
        if ($data['playlistSong'] !== false) {
            //Check if the user is logged in and has the required permissions
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $reviewerAuthenticated = $this->SecurityModel->authenticateReviewer();
            $userAuthorised = $reviewerAuthenticated || ($userAuthenticated && $this->PlaylistModel->getListOwnerById($data['playlistSong']->listId) == $this->SecurityModel->getCurrentUserId());
            if ($userAuthorised) {
                $data['body']  = 'playlistSong/showLog';
                $data['title'] = "Uber Rapsy | Historia nuty";
                $data['song'] = $this->SongModel->getSong($data['playlistSong']->songId);
                $data['songLog'] = $this->LogModel->getPlaylistSongLogs($playlistSongId);
                $data['redirectSource'] = $this->input->get('src');

                $this->load->view('templates/main', $data);
            }
            else redirect('logout');
        }
        else redirect('logout');
    }

    /**
     * Show song logs.
     *
     * @return void
     */
    public function showSongLog(): void
    {
        //Validate the submitted song id and fetch said song
        $songId = filter_var($this->input->get('songId'), FILTER_VALIDATE_INT);
        $data['song'] = $songId ? $this->SongModel->getSong($songId) : false;
        if ($data['song'] !== false) {
            //Check if the user is logged in and has the required permissions
            $userAuthenticated = $this->SecurityModel->authenticateReviewer();
            if ($userAuthenticated) {
                $data['body']  = 'playlistSong/showLog';
                $data['title'] = "Uber Rapsy | Historia nuty";
                $data['songLog'] = $this->LogModel->getSongLogs($songId);

                $this->load->view('templates/main', $data);
            }
            else redirect('logout');
        }
        else redirect('logout');
    }
}
