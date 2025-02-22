<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(!isset($_SESSION))
{
	session_start();
}

class YoutubeIntegration extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('PlaylistModel');
		$this->load->model('SongModel');
		$this->load->helper('cookie');
	}

	public function index()
	{
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();
        if($userAuthenticated) {
            $data = [];
            $data['body']  = 'adminDashboard';
            $data['title'] = "Uber Rapsy | Centrum Zarządzania";
        }
        else redirect('logout');

		$this->load->view('templates/main', $data);
	}

	public function generate()
    {
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();
        if ($userAuthenticated) {
            //Include google library
            $client = $this->SecurityModel->initialiseLibrary();
            $data = [];
            $data['body']  = 'invalidAction';
            $data['title'] = "Wystąpił Błąd!";

            //only proceed when the library was successfully included
            if ($client !== false) {
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
            else {
                //Could not load the library
                $data['errorMessage'] = "Nie znaleziono biblioteki google!";
            }
        }
        else redirect('logout');

        $this->load->view('templates/main', $data);
    }
	public function result()
	{
		$data = [];
        $data['body']  = 'refreshToken';
        $data['title'] = "Uzyskano nowy token!";

		$authCode = $_GET['code'] ?? 0;

		if($authCode != 0)
		{
            //Include google library
            $client = $this->SecurityModel->initialiseLibrary();

            if($client !== false)
            {
                //Exchange authorization code for an access token.
                $data['accessToken'] = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($data['accessToken']);
            }
            else
            {
                //Could not load the library
                $data['body']  = 'invalidAction';
                $data['title'] = "Wystąpił Błąd!";
                $data['errorMessage'] = "Nie znaleziono biblioteki google!";
            }
		}
		$this->load->view('templates/main', $data);
	}

}
