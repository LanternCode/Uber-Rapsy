<?php defined('BASEPATH') OR exit('No direct script access allowed');

if(!isset($_SESSION)){
    session_start();
}

class Welcome extends CI_Controller {

	public function __construct()
    {
        parent::__construct();
        $this->load->model('PlaylistModel');
        $this->load->model('AccountModel');
        $this->load->helper('cookie');
    }

    /**
     * Open homepage
     * @return void
     */
	public function index()
    {
		$data = array(
            'lists' => $this->PlaylistModel->GetAllPublicLists(),
            'body' => 'home',
            'title' => "Uber Rapsy | Portal do oceniania utworów rapowanych"
        );

        //Automatic sign-in
        $userLoggedIn = $_SESSION['userLoggedIn'] ?? false;
        if (isset($_COOKIE["login"]) && !$userLoggedIn) {
            $this->AccountModel->AutomaticSignIn();
        }

		$this->load->view( 'templates/main', $data );
	}

    public function TOS()
    {
        $data = [];

        $data['body']  = 'termsOfService';
        $data['title'] = "Zasady Użytkowania serwisu Uber Rapsy";

        $this->load->view( 'templates/main', $data );
    }

    public function testfunc()
    {
        $data = [];

        $data['body']  = 'test';
        $data['title'] = "Testing!";

        //$newReportId = $this->LogModel->SubmitReport("Hello");
        //print_r($newReportId);
        //die();

        $this->load->view( 'templates/main', $data );
    }
}
