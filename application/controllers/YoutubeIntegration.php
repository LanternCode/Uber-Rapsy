<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(!isset($_SESSION))
{
	session_start();
}

class YoutubeIntegration extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model( 'ListsModel' );
		$this->load->model( 'SongsModel' );
		$this->load->helper('cookie');
	}

	public function index()
	{
		$data = [];

		$data['body']  = 'youtubeDashboard';
		$data['title'] = "Uber Rapsy";

		$this->load->view( 'templates/main', $data );
	}

	public function result()
	{
		$data = [];

		$authCode = $_GET['code'] ?? 0;

		if($authCode != 0) {

			$myPath = $_SERVER['DOCUMENT_ROOT'] . '/Dev/Uber-Rapsy/';
			require_once $myPath . 'application/libraries/Google/vendor/autoload.php';

			$client = new Google\Client();

			$client->setAuthConfig($myPath . 'application/api/client_secret.json');

			// Exchange authorization code for an access token.
			$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
			print_r($accessToken);
			$client->setAccessToken($accessToken);
		}

		$data['body']  = 'youtubeDashboard';
		$data['title'] = "Dodaj playlistę";

		$this->load->view( 'templates/main', $data );
	}

}
