<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(!isset($_SESSION)){
    session_start();
}

class Account extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('AccountModel');
        $this->load->model('ListsModel');
        $this->load->helper('cookie');
    }

    public function index()
    {
        $data = [];
        $data['body'] = 'home';
        $data['invalidCredentials'] = 0;
        $userLoggedIn = $_SESSION['userLoggedIn'] ?? false;

        if(!$userLoggedIn)
        {
            $loginSuccess = true;

            $data['email'] = (isset($_POST['account--signin--email'])) ?
                trim(mysqli_real_escape_string($this->db->conn_id, $_POST['account--signin--email'])) : NULL;

            $data['password'] = (isset($_POST['account--signin--password'])) ?
                trim(mysqli_real_escape_string($this->db->conn_id, $_POST['account--signin--password'])) : NULL;

            if (isset($data['email']) && $data['email'] && filter_var($data['email'], FILTER_VALIDATE_EMAIL))
            {
                $userData = $this->AccountModel->getUserData($data['email']);
                $passwordToCompare = $userData->password ?? 0;
                //TODO: Try cracking with just a password of 0

                if ($passwordToCompare && password_verify($data['password'], $passwordToCompare))
                {
                    $_SESSION['userLoggedIn'] = 1;
                    $_SESSION['userRole'] = $userData->role;
                    $data['lists'] = $this->ListsModel->GetAllLists();
                }
                else
                {
                    $loginSuccess = false;
                    $data['invalidCredentials'] = 1;
                }
            }
            else $loginSuccess = false;

            if(!$loginSuccess)
            {
                $data['body'] = 'login';
            }
        }

        $this->load->view('templates/main', $data);
    }

    public function logout()
    {
        $data = [];
        $data['body'] = 'logout';

        session_unset();
        session_destroy();

        $this->load->view('templates/main', $data);
    }

}