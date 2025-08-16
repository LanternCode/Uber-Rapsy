<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!isset($_SESSION))
	session_start();

/**
 * Controller responsible for the integration between our platform and YouTube.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 *
 * @property SecurityModel $SecurityModel
 * @property CI_Input $input
 */
class YoutubeIntegration extends CI_Controller
{
	public function __construct()
    {
		parent::__construct();
	}

    /**
     * Opens the API administration dashboard.
     *
     * @return void
     */
	public function index(): void
	{
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();
        if ($userAuthenticated) {
            $data = array(
                'body' => 'adminDashboard',
                'title' => "Centrum zarządzania administratora",
                'userLoggedIn' => true,
                'isReviewer' => true
            );

            $this->load->view('templates/main', $data);
        }
        else redirect('errors/403-404');
	}

    /**
     * Generates a new YT API session refresh key.
     *
     * @return void
     */
	public function generate(): void
    {
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();
        if ($userAuthenticated) {
            $data = array(
                'body' => 'invalidAction',
                'title' => "Wystąpił błąd | Nie udało się załadować klienta YouTube API i biblioteki Google",
                'userLoggedIn' => true,
                'isReviewer' => true
            );

            //Proceed only if the library was successfully initialised
            $client = $this->SecurityModel->initialiseLibrary();
            if ($client !== false) {
                $client->addScope(Google_Service_Youtube::YOUTUBE);
                $client->setRedirectUri('http://localhost/Dev/Uber-Rapsy/apitestPlaylist');
                //Offline access will give you both an access and refresh tokens
                $client->setAccessType('offline');
                //Using "consent" ensures that the application always receives a refresh token
                //If you are not using offline access, you can omit this
                $client->setPrompt("consent");
                $client->setIncludeGrantedScopes(true);

                $auth_url = $client->createAuthUrl();
                header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
            }
            else
                $data['errorMessage'] = "Nie znaleziono biblioteki google!";

            $this->load->view('templates/main', $data);
        }
        else redirect('errors/403-404');
    }

    /**
     * Displays the new refresh token if one was generated.
     *
     * @return void
     */
	public function result(): void
	{
		$data = array(
            'body' => 'refreshToken',
            'title' => "Uzyskano nowy token odświeżający!",
            'userLoggedIn' => $this->SecurityModel->authenticateUser(),
            'isReviewer' => $this->SecurityModel->authenticateReviewer()
        );

        //Fetch the code returned by the API authenticator
		$authCode = $this->input->get('code');
		if ($authCode) {
            //Swap the code for a refresh token
            $client = $this->SecurityModel->initialiseLibrary();
            if ($client !== false) {
                //Exchange authorization code for an access token.
                $data['accessToken'] = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($data['accessToken']);
            }
            else {
                //Could not load the library
                $data['body']  = 'invalidAction';
                $data['title'] = "Wystąpił błąd | Nie udało się odświeżyć tokenu ponieważ nie znaleziono biblioteki Google";
                $data['errorMessage'] = "Nie znaleziono biblioteki YouTube API!";
            }
		}

		$this->load->view('templates/main', $data);
	}
}
