<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!isset($_SESSION)) {
    session_start();
}

class Log extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->model('SecurityModel');
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
        $reportId = isset($_GET['repId']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['repId'])) : 0;
        $reportId = is_numeric($reportId) ? $reportId : 0;

        //Check if the provided report is is valid
        if($reportId) {
            //Check if the user is logged in and has the required permissions
            $userAuthenticated = $this->SecurityModel->authenticateUser();
            $userAuthorised = $userAuthenticated && $this->LogModel->GetReportOwnerById($reportId) == $_SESSION['userId'];
            if ($userAuthorised) {
                $data['report'] = $this->LogModel->FetchReport($reportId);
            }
            else redirect('logout');
        }
        else redirect('logout');

        $this->load->view('templates/customNav', $data);
    }

}
