<?php defined('BASEPATH') OR exit('No direct script access allowed');

if(!isset($_SESSION)){
    session_start();
}

class Welcome extends CI_Controller
{
	public function __construct()
    {
        parent::__construct();
        $this->load->model('PlaylistModel');
        $this->load->model('AccountModel');
        $this->load->helper('cookie');
    }

    /**
     * Display the homepage
     * @return void
     */
	public function index()
    {
		$data = array(
            'lists' => $this->PlaylistModel->fetchHomepagePlaylists(),
            'body' => 'home',
            'title' => "Uber Rapsy | Portal do oceniania utworów rapowanych"
        );

        //Automatic sign-in
        $userLoggedIn = $_SESSION['userLoggedIn'] ?? false;
        if (isset($_COOKIE["login"]) && !$userLoggedIn) {
            $this->AccountModel->AutomaticSignIn();
        }

		$this->load->view('templates/main', $data);
	}

    /**
     * Display the Terms of Service page
     * @return void
     */
    public function TOS()
    {
        $data = array(
            'body' => 'termsOfService',
            'title' => "Uber Rapsy | Zasady Użytkowania serwisu Uber Rapsy"
        );

        $this->load->view('templates/main', $data);
    }

    /**
     * Test-specific route
     * @return void
     */
    public function testfunc()
    {
        $data = array(
            'body' => 'test',
            'title' => "Uber Rapsy | Test!"
        );

        //$newReportId = $this->LogModel->SubmitReport("Hello");
        //print_r($newReportId);
        //die();

        $this->load->view( 'templates/main', $data );
    }

    /**
     * Maintenance-specific route
     * @return void
     */
    function maintenance()
    {
        $this->output->set_status_header('503');
        $this->load->view('maintenance');
    }
}
