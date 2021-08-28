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
		//include google library
		$myPath = $_SERVER['DOCUMENT_ROOT'] . '/Dev/Uber-Rapsy/';
		require_once $myPath . 'application/libraries/Google/vendor/autoload.php';
		$client = new Google\Client();
		$token_expired = false;

		//get the currently saved token from the cookie
		$accessToken = get_cookie("UberRapsyToken");
		//Check if the cookie contained the token
		if (!is_null($accessToken)) {
			try {
				//If yes, check if it is valid and not expired
				$client->setAccessToken($accessToken);
				$token_expired = $client->isAccessTokenExpired();
			} catch (Exception $e) {
				//exception raised means the format is invalid
				$token_expired = true;
			}
		} else {
			//cookie did not exist or returned null
			$token_expired = true;
		}

		//if the token expired, fetch the refresh token and attempt a refresh
		if($token_expired)
		{
			//first fetch the refresh token from api/refresh_token.txt
			if($refresh_token = file_get_contents("application/api/refresh_token.txt")) {
				//get a new token
				$client->setAuthConfig($myPath . 'application/api/client_secret.json');
				$client->refreshToken($refresh_token);
				//save the new token
				$accessToken = $client->getAccessToken();
				//run JSON encode to store the token in a cookie
				$accessToken = json_encode($accessToken);
				//delete the old cookie with the expired token
				delete_cookie("UberRapsyToken");
				//set a new cookie with the new token
				set_cookie("UberRapsyToken", $accessToken, 86400);
			} else {
				//refresh token not found - contact an administrator!
				$client->setAuthConfig($myPath . 'application/api/client_secret.json');
				$client->addScope(Google_Service_Youtube::YOUTUBE);
				$client->setRedirectUri('http://localhost/Dev/Uber-Rapsy/apitestPlaylist');
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
		}
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
