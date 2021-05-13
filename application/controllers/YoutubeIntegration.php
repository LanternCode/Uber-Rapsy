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
		$myPath = $_SERVER['DOCUMENT_ROOT'] . '/Dev/UberRapsy/';
		require_once $myPath . 'application/libraries/Google/vendor/autoload.php';

		$client = new Google\Client();

		$client->setAuthConfig($myPath . 'application/api/client_secret.json');
		$client->addScope(Google_Service_Youtube::YOUTUBE);
		$client->setRedirectUri('http://localhost/Dev/UberRapsy/apitestPlaylist');
		// offline access will give you both an access and refresh token so that
		// your app can refresh the access token without user interaction.
		$client->setAccessType('offline');
		// Using "consent" ensures that your application always receives a refresh token.
		// If you are not using offline access, you can omit this.
		$client->setPrompt("consent");
		$client->setIncludeGrantedScopes(true);   // incremental auth

		$auth_url = $client->createAuthUrl();
		header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
	}

	public function result()
	{
		$data = [];

		$authCode = isset( $_GET['code'] ) ? $_GET['code'] : 0;

		if($authCode != 0) {

			$myPath = $_SERVER['DOCUMENT_ROOT'] . '/Dev/UberRapsy/';
			require_once $myPath . 'application/libraries/Google/vendor/autoload.php';

			$client = new Google\Client();

			$client->setAuthConfig($myPath . 'application/api/client_secret.json');

			// Exchange authorization code for an access token.
			$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
			$client->setAccessToken($accessToken);

			//Save the authorization token into the session object
			$_SESSION['token'] = $accessToken;
			set_cookie("UberRapsyToken", $accessToken, 86400);
		}

		$data['body']  = 'youtubeDashboard';
		$data['title'] = "Dodaj playlistę";

		$this->load->view( 'templates/main', $data );
	}

}
