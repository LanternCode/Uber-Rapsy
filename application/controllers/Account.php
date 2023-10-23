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

        if (!$userLoggedIn) {
            if (isset($_COOKIE["login"])) {
                $email = json_decode($_COOKIE["login"])->userEmail;
                $password = json_decode($_COOKIE["login"])->userPassword;
            }
            else {
                $email = isset($_POST['userEmail']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['userEmail'])) : NULL;
                $password = isset($_POST['userPassword']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['userPassword'])) : NULL;
            }

            //Only attempt the login once the form is submitted or the cookie is set
            if (isset($email) && $email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $loginSuccess = $this->AccountModel->SignIn($email, $password);
                if($loginSuccess) {
                    $loginSessionDetails = array(
                        'userEmail' => $email,
                        'userPassword' => $password
                    );
                    setcookie("login", json_encode($loginSessionDetails), time() + (86400 * 7), "/");
                    redirect(base_url());
                }
                else $data['invalidCredentials'] = 1;
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