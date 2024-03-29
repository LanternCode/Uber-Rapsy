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

        //Try to sign the user in if their details are saved
        if (isset($_COOKIE["login"])) {
            $this->AccountModel->AutomaticSignIn();
        }

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
