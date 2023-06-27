<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!isset($_SESSION)) {
    session_start();
}

class Log extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('LogModel');
    }

    /**
     * This function fetches a report and passes it to be displayed by the user
     *
     * @return void
     */
    public function displayReport()
    {
        $data = [];
        $data['body'] = 'showReport';
        $data['title'] = "Uber Rapsy | Widok Raportu";

        $userAuthenticated = $this->authenticateUser();
        if($userAuthenticated) {
            $reportId = isset($_GET['repId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['repId'])) : 0;
            if($reportId) {
                $data['report'] = $this->LogModel->FetchReport($reportId);
            }
            else {
                $data['body']  = 'invalidAction';
                $data['title'] = "Błąd akcji!";
                $data['errorMessage'] = "Nie znaleziono raportu o podanym numerze id!";
            }
        }
        else redirect('logout');

        $this->load->view('templates/customNav', $data);
    }

    /**
     * Checks whether the user is logged in and has the appropriate role.
     *
     * @return boolean     true if authenticated, false if not
     */
    function authenticateUser(): bool
    {
        $userLoggedIn = $_SESSION['userLoggedIn'] ?? 0;
        $userRole = $_SESSION['userRole'] ?? 0;

        if($userLoggedIn === 1 && $userRole === 'reviewer')
        {
            return true;
        }
        else return false;
    }
}
