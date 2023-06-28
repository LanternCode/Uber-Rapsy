<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!isset($_SESSION)) {
    session_start();
}

class Log extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
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

        $userAuthenticated = $this->SecurityModel->authenticateUser();
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

}
