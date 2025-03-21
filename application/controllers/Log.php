<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!isset($_SESSION)) {
    session_start();
}

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
            $userAuthorised = $userAuthenticated && $this->PlaylistModel->GetListOwnerById($playlistId) == $_SESSION['userId'];
            if ($userAuthorised) {
                $data = array(
                    'body' => 'playlist/showLog',
                    'title' => 'Uber Rapsy | Historia playlisty',
                    'playlist' => $this->PlaylistModel->fetchPlaylistById($playlistId),
                    'playlistLog' => $this->LogModel->GetPlaylistLog($playlistId),
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
        $songId = $this->input->get('songId');
        $data['song'] = $songId ? $this->SongModel->GetSongById($songId) : false;
        if ($data['song'] !== false) {
            //Check if the user is logged in and has the required permissions
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = $userAuthenticated && $this->PlaylistModel->GetListOwnerById($data['song']->ListId) == $_SESSION['userId'];
            if ($userAuthorised) {
                $data['body']  = 'song/showLog';
                $data['title'] = "Uber Rapsy | Historia nuty";
                $data['songLog'] = $this->LogModel->GetSongLog($songId);
                $data['redirectSource'] = $this->input->get('src');

                $this->load->view('templates/main', $data);
            }
            else redirect('logout');
        }
        else redirect('logout');
    }
}
