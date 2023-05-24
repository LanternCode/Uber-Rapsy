<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(!isset($_SESSION)){
    session_start();
}

class Welcome extends CI_Controller {

	public function __construct()
	{
        parent::__construct();
        $this->load->model('PlaylistModel');
    }

	public function index()
	{
		$data = [];

		$data['lists'] = $this->PlaylistModel->GetAllPublicLists();

		$data['body']  = 'home';
		$data['title'] = "Uber Rapsy | Portal do oceniania utworów rapowanych";

		$this->load->view( 'templates/customNav', $data );

	}

    public function testfunc()
    {
        $data = [];

        $data['body']  = 'test';
        $data['title'] = "Testing!";

        $this->load->view( 'templates/main', $data );
    }
}
