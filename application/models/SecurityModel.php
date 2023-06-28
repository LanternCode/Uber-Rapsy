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
    }

    /**
     * Checks whether the user is logged in and has the appropriate role.
     *
     * @return boolean     true if authenticated, false if not
     */
    function authenticateUser(): bool
    {
        $userLoggedIn = $_SESSION['userLoggedIn'] ?? 0;
        $userRole = $_SESSION['userRole'] ?? 0;

        if($userLoggedIn === 1 && $userRole === 'reviewer')
        {
            return true;
        }
        else return false;
    }

}