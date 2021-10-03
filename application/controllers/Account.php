<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(!isset($_SESSION)){
    session_start();
}

/**
 * Controller responsible for handling views related with user's account.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 */
class Account extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('AccountModel');
        $this->load->model('PlaylistModel');
        $this->load->helper('cookie');
    }

    /**
     * Handles logins.
     *
     * @return void
     */
    public function index()
    {
        $data = [];
        $data['body'] = 'login';
        $userLoggedIn = $_SESSION['userLoggedIn'] ?? false;

        if(!$userLoggedIn)
        {
            $data['email'] = isset($_POST['userEmail']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['userEmail'])) : NULL;
            $data['password'] = isset($_POST['userPassword']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['userPassword'])) : NULL;

            if (isset($data['email']) && $data['email'] && filter_var($data['email'], FILTER_VALIDATE_EMAIL))
            {
                $userData = $this->AccountModel->GetUserData($data['email']);
                $passwordToCompare = $userData->password ?? 0;
                //TODO: Try cracking with just a password of 0

                if ($passwordToCompare && password_verify($data['password'], $passwordToCompare))
                {
                    $_SESSION['userLoggedIn'] = 1;
                    $_SESSION['userRole'] = $userData->role;
                    redirect(base_url());
                }
                else
                {
                    $data['invalidCredentials'] = 1;
                }
            }
        }

        $this->load->view('templates/main', $data);
    }

    /**
     * Handles logouts.
     *
     * @return void
     */
    public function logout()
    {
        $data = array(
            'body' => 'logout'
        );

        session_unset();
        session_destroy();

        $this->load->view('templates/main', $data);
    }

}