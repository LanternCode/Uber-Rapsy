<?php defined('BASEPATH') OR exit('No direct script access allowed');

if(!isset($_SESSION)){
    session_start();
}

/**
 * Class responsible for the security of the application.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 */
class SecurityModel extends CI_Model
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('cookie');
        $this->load->model('AccountModel');
    }

    /**
     * Checks whether the user is logged in.
     *
     * @return boolean     true if authenticated, false otherwise
     */
    function authenticateUser(): bool
    {
        //Automatic sign-in
        $userLoggedIn = $_SESSION['userLoggedIn'] ?? false;
        if (isset($_COOKIE["login"]) && !$userLoggedIn) {
            $this->AccountModel->automaticSignIn();
        }

        //Fetch user credentials
        $userLoggedIn = $_SESSION['userLoggedIn'] ?? 0;
        if($userLoggedIn)
            return true;
        else return false;
    }

    /**
     * Checks whether a reviewer is logged in
     *
     * @return boolean     true if authenticated, otherwise false
     */
    function authenticateReviewer(): bool
    {
        $userLoggedIn = $_SESSION['userLoggedIn'] ?? 0;
        $userRole = $_SESSION['userRole'] ?? 0;

        if($userLoggedIn === 1 && $userRole === 'reviewer')
            return true;
        else return false;
    }

    /**
     * Check if the user is in the debugging mode
     *
     * @return bool
     */
    function debuggingEnabled(): bool
    {
        $userCanDebug = $this->authenticateReviewer();
        if($userCanDebug) {
            return $_SESSION['debuggingEnabled'] ?? 0;
        }
        else return false;
    }

    /**
     * The function tries to load the Google library and initialise the client.
     * If successful, the client is returned. Otherwise - false.
     *
     * @return false|\Google\Client
     */
    function initialiseLibrary(): \Google\Client|bool
    {
        try {
            $myPath = $_SERVER['DOCUMENT_ROOT'] . (ENVIRONMENT !== 'production' ? '/Dev' : '') . '/Uber-Rapsy/';
            require_once $myPath . 'vendor/autoload.php';
            $client = new Google\Client();
            $client->setAuthConfig($myPath . 'application/api/client_secret.json');
            return $client;
        } catch(Exception $e) {
            //The library or the client could not be initiated
            return false;
        }
    }

    /**
     * Validates the google oauth2 token.
     *
     * @return bool true if token is valid, false if expired.
     */
    function validateAuthToken($client): bool
    {
        //Get the currently saved token from the cookie
        $accessToken = get_cookie("UberRapsyToken");
        //Check if the cookie contained the token
        $token_expired = false;
        if (!is_null($accessToken)) {
            try {
                //If yes, check if it is valid and not expired
                $client->setAccessToken($accessToken);
                $token_expired = $client->isAccessTokenExpired();
            } catch (Exception $e) {
                //Exception raised means the format is invalid
                $token_expired = true;
            }
        } else {
            //Cookie did not exist or returned null
            $token_expired = true;
        }

        //If the token expired, fetch the refresh token and attempt a refresh
        if($token_expired)
        {
            //First fetch the refresh token from api/refresh_token.txt
            if($refresh_token = file_get_contents("application/api/refresh_token.txt")) {
                //Get a new token
                $client->refreshToken($refresh_token);
                //Save the new token
                $accessToken = $client->getAccessToken();
                //Run JSON encode to store the token in a cookie
                $accessToken = json_encode($accessToken);
                //Delete the old cookie with the expired token
                delete_cookie("UberRapsyToken");
                //Set a new cookie with the new token
                set_cookie("UberRapsyToken", $accessToken, 86400);
                //Set token_expired to false and proceed
                $token_expired = false;
            }
        }

        return $token_expired;
    }
}