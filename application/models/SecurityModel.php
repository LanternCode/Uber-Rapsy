<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!isset($_SESSION))
    session_start();

/**
 * Model responsible for managing the application's security, mostly user authentication and authorisation.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/RAPPAR/
 *
 * @property AccountModel $AccountModel
 */
class SecurityModel extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('cookie');
        $this->load->model('AccountModel');
    }

    /**
     * Check if the user is logged in.
     *
     * @return bool
     */
    public function authenticateUser(): bool
    {
        //Automatic sign-in
        $userLoggedIn = $_SESSION['userLoggedIn'] ?? false;
        if (isset($_COOKIE["login"]) && !$userLoggedIn)
            $this->AccountModel->automaticSignIn();

        //Fetch user credentials after an automatic sign-in attempt
        $userLoggedIn = $_SESSION['userLoggedIn'] ?? false;
        if ($userLoggedIn) {
            $userId = $this->getCurrentUserId();
            $accountLocked = $this->AccountModel->getUserAccountStatus($userId);
            if ($accountLocked) {
                redirect('logout');
                return false;
            }
            else return true;
        }
        else return false;
    }

    /**
     * Check if a reviewer is logged in.
     *
     * @return bool
     */
    public function authenticateReviewer(): bool
    {
        $userLoggedIn = $this->authenticateUser();
        $userRole = $this->getCurrentUserRole();

        if ($userLoggedIn == 1 && $userRole == 'reviewer')
            return true;
        else return false;
    }

    /**
     * Check if the user is in the debugging mode.
     * Debugging mode can be enabled by RAPPAR staff in the account settings.
     *
     * @return bool
     */
    public function debuggingEnabled(): bool
    {
        $userCanDebug = $this->authenticateReviewer();
        if ($userCanDebug) {
            return $_SESSION['debuggingEnabled'] ?? 0;
        }
        else return false;
    }

    /**
     * Import the Google library and initialise the client.
     *
     * @return false|\Google\Client false if the client could not be initialised
     */
    public function initialiseLibrary(): \Google\Client|bool
    {
        try {
            //Import the library
            $myPath = $_SERVER['DOCUMENT_ROOT'].(ENVIRONMENT !== 'production' ? '/Dev' : '').'/RAPPAR/';
            require_once $myPath . 'vendor/autoload.php';

            //Initialise the client
            $client = new Google\Client();

            //Supply the client with the required API credentials
            $client->setAuthConfig($myPath.'application/api/client_secret.json');
            return $client;
        }
        catch (Exception $e) {
            //The library or the client could not be initiated
            return false;
        }
    }

    /**
     * Validate the Google OAuth2 token.
     * If the token is invalid or expired, attempt a refresh.
     *
     * @param object $client the Google client obtained from the initialiseLibrary method.
     * @return bool true if the token is valid, false if it expired and could not be refreshed.
     */
    public function validateAuthToken(object $client): bool
    {
        //Get the token cookie
        $accessToken = get_cookie("RapparToken");

        //Check if the cookie contains the token
        $token_expired = false;
        if (!is_null($accessToken)) {
            try {
                //If the cookie contains the token, check if it is valid (not expired)
                $client->setAccessToken($accessToken);
                $token_expired = $client->isAccessTokenExpired();
            }
            catch (Exception $e) {
                //Exception raised - the token is invalid
                $token_expired = true;
            }
        }
        else {
            //Cookie does not exist
            $token_expired = true;
        }

        //If the user token expired, fetch the refresh token and get a new user token
        if ($token_expired) {
            //Fetch the refresh token from api/refresh_token.txt
            if ($refresh_token = file_get_contents("application/api/refresh_token.txt")) {
                //Get a new user token
                $client->refreshToken($refresh_token);
                $accessToken = $client->getAccessToken();
                //Delete the old cookie
                delete_cookie("RapparToken");
                //Encode the user token to store it as a cookie
                $accessToken = json_encode($accessToken);
                //Set a cookie with the new user token valid for one day
                set_cookie("RapparToken", $accessToken, 86400);
                $token_expired = false;
            }
        }

        return $token_expired;
    }

    /**
     * Fetch the current user's id.
     *
     * @return int|bool false if no user is logged in.
     */
    public function getCurrentUserId(): int|bool
    {
        return $_SESSION['userId'] ?? false;
    }

    /**
     * Fetch the current user's username.
     *
     * @return int|bool false if no user is logged in.
     */
    public function getCurrentUserName(): int|bool
    {
        return $_SESSION['username'] ?? false;
    }

    /**
     * Fetch the current user's role.
     *
     * @return string|bool false if no user is logged in.
     */
    public function getCurrentUserRole(): string|bool
    {
        return $_SESSION['userRole'] ?? false;
    }
}