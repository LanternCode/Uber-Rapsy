<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function __construct()
	{
        parent::__construct();
        $this->load->model( 'ListsModel' );
		$this->load->model( 'SongsModel' );
    }

	public function index()
	{
		$data = [];

		$data['lists'] = $this->ListsModel->GetAllLists();

		$data['body']  = 'home';
		$data['title'] = "Homepage!";

		$this->load->view( 'templates/main', $data );

	}
}
