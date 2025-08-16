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
            $userId = $this->SecurityModel->getCurrentUserId();
            $userAuthorised = $userAuthenticated && $this->LogModel->getReportOwnerById($reportId) == $userId;
            if ($userAuthorised) {
                $data = array(
                    'body' => 'showReport',
                    'title' => 'Widok raportu | Przeglądasz jeden z raportów dotyczących treści dodanej przez użytkowników',
                    'report' => $this->LogModel->fetchReport($reportId)
                );
                $this->load->view('templates/customNav', $data);
            }
            else redirect('errors/403-404');
        }
        else redirect('errors/403-404');
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
            $userId = $this->SecurityModel->getCurrentUserId();
            $userAuthorised = $userAuthenticated && $this->PlaylistModel->getListOwnerById($playlistId) == $userId;
            if ($userAuthorised) {
                $data = array(
                    'body' => 'playlist/showLog',
                    'playlist' => $this->PlaylistModel->fetchPlaylistById($playlistId),
                    'playlistLog' => $this->LogModel->getPlaylistLog($playlistId),
                    'redirectSource' => $this->input->get('src'),
                    'userLoggedIn' => true,
                    'isReviewer' => $this->SecurityModel->authenticateReviewer()
                );
                $data['title'] = 'Historia playlisty '.$data['playlist']->ListName;

                $this->load->view('templates/main', $data);
            }
            else redirect('errors/403-404');
        }
        else redirect('errors/403-404');
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
                $data['song'] = $this->SongModel->getSong($data['playlistSong']->songId);
                $data['title'] = "Historia utworu ".$data['song']->SongTitle." | Moje Playlisty";
                $data['songLog'] = $this->LogModel->getPlaylistSongLogs($playlistSongId);
                $data['redirectSource'] = $this->input->get('src');
                $data['userLoggedIn'] = $userAuthenticated;
                $data['isReviewer'] = $reviewerAuthenticated;

                $this->load->view('templates/main', $data);
            }
            else redirect('errors/403-404');
        }
        else redirect('errors/403-404');
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
                $data['body']  = 'song/songLog';
                $data['title'] = "Historia utworu ".$data['song']->SongTitle;
                $data['songLog'] = $this->LogModel->getSongLogs($songId);
                $data['userLoggedIn'] = true;
                $data['isReviewer'] = true;

                $this->load->view('templates/main', $data);
            }
            else redirect('errors/403-404');
        }
        else redirect('errors/403-404');
    }
}
