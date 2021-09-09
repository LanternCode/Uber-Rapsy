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

        if(isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn'] && isset($_SESSION['userRole']) && $_SESSION['userRole'] == "reviewer")
        {
            $data['body']  = 'youtubeDashboard';
            $data['title'] = "Uber Rapsy";
        }
        else
        {
            //The user is not allowed to update anything in the system
            $data['body']  = 'invalidAction';
            $data['title'] = "Wystąpił Błąd!";
            $data['errorMessage'] = "Nie posiadasz uprawnień do wykonywania tej akcji.";
        }

		$this->load->view( 'templates/main', $data );
	}

	public function generate()
    {
        $data = [];
        $data['body']  = 'invalidAction';
        $data['title'] = "Wystąpił Błąd!";

        if(isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn'] && isset($_SESSION['userRole']) && $_SESSION['userRole'] == "reviewer")
        {
            //include google library
            $library_included = true;
            try {
                $myPath = $_SERVER['DOCUMENT_ROOT'] . '/Dev/Uber-Rapsy/';
                require_once $myPath . 'application/libraries/Google/vendor/autoload.php';
                $client = new Google\Client();
                $client->setAuthConfig($myPath . 'application/api/client_secret.json');
            } catch(Exception $e) {
                //The library or the client could not be initiated
                $library_included = false;
            }

            //only proceed when the library was successfully included
            if($library_included)
            {
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
            else
            {
                //could not load the library
                $data['errorMessage'] = "Nie znaleziono biblioteki google!";
            }
        }
        else
        {
            //The user is not allowed to update anything in the system
            $data['errorMessage'] = "Nie posiadasz uprawnień do wykonywania tej akcji.";
        }
        $this->load->view( 'templates/main', $data );
    }
	public function result()
	{
		$data = [];
        $data['body']  = 'refreshToken';
        $data['title'] = "Uzyskano nowy token!";

		$authCode = $_GET['code'] ?? 0;

		if($authCode != 0)
		{
            //include google library
            $library_included = true;
            try {
                $myPath = $_SERVER['DOCUMENT_ROOT'] . '/Dev/Uber-Rapsy/';
                require_once $myPath . 'application/libraries/Google/vendor/autoload.php';
                $client = new Google\Client();
                $client->setAuthConfig($myPath . 'application/api/client_secret.json');
            } catch(Exception $e) {
                //The library or the client could not be initiated
                $library_included = false;
            }

            //only proceed when the library was successfully included
            if($library_included)
            {
                // Exchange authorization code for an access token.
                $data['accessToken'] = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($data['accessToken']);
            }
            else
            {
                //could not load the library
                $data['body']  = 'invalidAction';
                $data['title'] = "Wystąpił Błąd!";
                $data['errorMessage'] = "Nie znaleziono biblioteki google!";
            }
		}
		$this->load->view( 'templates/main', $data );
	}

}
