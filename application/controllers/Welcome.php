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
		// $data['GM']            = $this->UserModel->isUserGamemaster( $_SESSION['userId'], $data['sessionId'] ) ? 1 : 0;
		// $data['GMViewEnabled'] = isset( $_SESSION['GMViewEnabled'] ) ? $_SESSION['GMViewEnabled'] : ( $data['GM'] ? 1 : 0 );
		// $data['session']       = $this->Assignment_model->Get_all_session_information( $data['sessionId'] );
		// $data['participants']  = $this->Assignment_model->getAllParticipantsInformation( $data['sessionId'] );
		// $data['rolls']         = $this->Roll_model->getRollHistory( $data['sessionId'] );
		// $data['dices']         = explode( ',', $data['session']->dices );

		// $data['myParticipantName'] = "Player X";
		// $data['sessionGamemasterName'] = "The GameMaster";
		// foreach( $data['participants'] as $participant )
		// {
		// 	if( $participant->userId == $_SESSION['userId'] )
		// 		$data['myParticipantName'] = $participant->name;
		//
		// 	if( $participant->rank == 1 )
		// 		$data['sessionGamemasterName'] = $participant->name;
		// }

		//get all data related to the enemies
		$data['lists'] = $this->ListsModel->GetAllLists();

		$data['body']  = 'home';
		$data['title'] = "Homepage!";

		$this->load->view( 'templates/main', $data );

	}
}
