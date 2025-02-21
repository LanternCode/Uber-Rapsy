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
     * Handles new account registrations.
     *
     * @return void
     */
    public function newAccount()
    {
        $data = [];
        $data['body'] = 'register';
        $userLoggedIn = $_SESSION['userLoggedIn'] ?? false;
        $formSubmitted = isset($_POST['formSubmitted']);

        if (!$userLoggedIn && $formSubmitted) {
            $username		= isset($_POST['register--username']) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_POST['register--username'] ) ) : "";
            $email			= isset($_POST['register--email']) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_POST['register--email'] ) ) : 0;
            $password		= isset($_POST['register--password']) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_POST['register--password'] ) ) : 0;
            $passwordRep 	= isset($_POST['register--password__repetition']) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_POST['register--password__repetition'] ) ) : 0;
            $termsOfService	= isset($_POST['register--TOS']) ? trim( mysqli_real_escape_string( $this->db->conn_id, $_POST['register--TOS'] ) ) : 0;

            $data['usernameTooShort'] = strlen( $username ) > 0  ? 0 : "Nazwa użytkownika jest wymagana!";
            $data['usernameTooLong']  = strlen( $username ) > 20 ? "Nazwa użytkownika nie może być dłuższa niż 20 znaków." : 0;

            $data['emailFormatInvalid'] = filter_var( $email, FILTER_VALIDATE_EMAIL ) ? 0 : "Adres email jest wymagany!";
            $data['emailTooLong'] 		= strlen( $email ) > 50 ? "Adres email nie może być dłuższy niż 50 znaków." : 0;
            $data['emailRepeated'] 		= $this->AccountModel->isEmailUnique( $email ) ? 0 : "Istnieje już konto zarejestrowane na ten adres email. Jeżeli jest Twoje, wróć do ekranu logowania i wciśnij 'przypomnij hasło'.";

            $data['passwordTooShort'] = strlen( $password ) > 3 ? 0 : "Hasło musi zawierać przynajmniej 4 znaki!";
            $data['passwordTooLong']  = strlen( $password ) > 25 ? "Hasło nie może być dłuższe niż 25 znaków." : 0;

            $data['passwordRepetitionNotMatching'] = $password == $passwordRep ? 0 : "Wpisane hasła nie są identyczne!";

            $data['termsOfServiceDenied'] = !$termsOfService ? "Aby kontynuować musisz zaakceptować zasady korzystania z serwisu." : 0;

            if( $data['usernameTooShort'] || $data['usernameTooLong'] ||
                $data['emailFormatInvalid'] || $data['emailTooLong'] || $data['emailRepeated'] ||
                $data['passwordTooShort'] || $data['passwordTooLong'] ||
                $data['passwordRepetitionNotMatching'] ||
                $data['termsOfServiceDenied'] )
            {

                $dataKeys = array_keys($data);
                for ($i = 0; $i < count($dataKeys); ++$i) {
                    if (!$data[$dataKeys[$i]]) $data[$dataKeys[$i]] = "";
                    else if ($data[$dataKeys[$i]] != "register") { //Exclude the 'body' param
                        $addInFront = "<h4 class='registrationError'>";
                        $addInFront .= $data[$dataKeys[$i]];
                        $addInFront .= "</h4>";
                        $data[$dataKeys[$i]] = $addInFront;
                    }
                }

                $data['setUsername']      	   = ($data['usernameTooShort'] || $data['usernameTooLong']) ? "" : $username;
                $data['setEmail']			   = ($data['emailFormatInvalid'] || $data['emailTooLong'] || $data['emailRepeated']) ? "" : $email;
                $data['setPassword']	  	   = ($data['passwordTooShort'] || $data['passwordTooLong']) ? "" : $password;
                $data['setPasswordRepetition'] = ($data['setPassword'] && !$data['passwordRepetitionNotMatching']) ? $password : "";
                $data['setTOS']                = $data['termsOfServiceDenied'] ? "" : "checked";
            }
            else {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $data['userHasRegistered'] = 1;
                $data['body'] = 'registrationSuccessful';
                $queryData['username'] = $email;
                $queryData['email'] = $email;
                $queryData['password'] = $passwordHash;
                $queryData['role'] = "user";
                $this->AccountModel->registerNewUser($queryData);

                //Automatically sign the user in after registration
                session_unset();
                session_destroy();
                $authSuccess = $this->AccountModel->SignIn($email, $password);
                $loginSessionDetails = array(
                    'userEmail' => $email,
                    'userPassword' => $password
                );
                setcookie("login", json_encode($loginSessionDetails), time() + (86400 * 7), "/");
            }
        }
        else if ($userLoggedIn)
            redirect('logout');

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

        //Delete the user session
        session_unset();
        session_destroy();

        //Delete the 'do not sign me out' cookie
        if (isset($_COOKIE['login'])) {
            unset($_COOKIE['login']);
        }
        setcookie("login", "", time() - 3600, "/");

        $this->load->view('templates/main', $data);
    }

    public function forgottenPassword()
    {
        //If the user is already logged in, reset the session
        $userLoggedIn = $_SESSION['userLoggedIn'] ?? false;
        if ($userLoggedIn)
            redirect('logout');

        $data = array(
            'title' => 'Przypomnij Hasło | Uber Rapsy',
            'body' => 'account/forgotPassword'
        );

        //Verify the provided email address
        $enteredEmail = isset($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['email'])) : "";
        if ($enteredEmail) {
            if (!$this->AccountModel->isEmailUnique($enteredEmail)) {
                if($resetKey = $this->AccountModel->insertPasswordUpdateLink($enteredEmail)) {
                    $this->AccountModel->sendPasswordChangeEmail($enteredEmail, $resetKey);
                    $data['actionNotification'] = "<span class='universal--successMessage'>Jeżeli istnieje konto założone na ten adres email, została na niego wysłana wiadomość z linkiem resetującym hasło.</span>";
                }
                else $data['actionNotification'] = "Nie udało się wysłać linku resetującego hasło. Spróbuj ponownie później bądź skontaktuj się z administracją RAPPAR.";
            }
            else $data['actionNotification'] = "<span class='universal--successMessage'>Jeżeli istnieje konto założone na ten adres email, została na niego wysłana wiadomość z linkiem resetującym hasło.</span>";
        }
        elseif (isset($_POST['email']))
            $data['actionNotification'] = "Wpisano niepoprawny adres email.";

        $this->load->view('templates/main', $data);
    }

    public function resetPassword()
    {
        //If the user is already logged in, reset the session
        $userLoggedIn = $_SESSION['userLoggedIn'] ?? false;
        if ($userLoggedIn)
            redirect('logout');

        $data = array(
            'title' => 'Zresetuj Hasło | Uber Rapsy',
            'body' => 'account/resetPassword',
            'errorMessage' => '',
            'key' => isset($_GET['qs']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_GET['qs'])) : ""
        );

        //Check if a valid password reset key was provided
        $userId = $this->AccountModel->validatePasswordResetString($data['key']);
        if ($userId) {
            $password = isset($_POST['newPassword']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['newPassword'])) : null;
            $passwordRepeat = isset($_POST['newPasswordRepeated']) ? trim(mysqli_real_escape_string($this->db->conn_id, $_POST['newPasswordRepeated'])) : null;

            //Process the form if it was submitted
            if ($password && $passwordRepeat && strlen($password) > 7 && $password == $passwordRepeat) {
                $this->AccountModel->updateUserPassword($password, $userId);
                $data['body'] = 'account/resetPasswordResult';
                $data['result'] = "Pomyślnie zresetowano hasło! Możesz teraz się zalogować!";
            }
            elseif (isset($_POST['newPassword'])) {
                if (!$password)
                    $data['errorMessage'] = "Musisz wpisać nowe hasło.";
                elseif (strlen($password) < 8)
                    $data['errorMessage'] = "Nowe hasło musi zawierać przynajmniej osiem (8) znaków.";
                elseif (!$passwordRepeat)
                    $data['errorMessage'] = "Musisz ponownie wpisać nowe hasło.";
                elseif ($password !== $passwordRepeat)
                    $data['errorMessage'] = "Wpisane hasła nie są identyczne.";

                $data['errorMessage'] .= "<br />";
            }
        }
        else {
            $data['body'] = 'account/resetPasswordResult';
            $data['result'] = "Podany link jest niepoprawny. Spróbuj jeszcze raz lub ponownie kliknij przycisk przypomnij hasło w panelu logowania i wpisz swój adres email.";
        }

        $this->load->view('templates/main', $data);
    }

    /**
     * Opens the users dashboard (with only safe data presented)
     *
     * @return void
     */
    public function usersDashboard()
    {
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();
        if($userAuthenticated) {
            $data = array(
                'body' => 'admin/usersDashboard',
                'title' => 'Uber Rapsy | Centrum Zarządzania Użytkownikami',
                'users' => $this->AccountModel->fetchAllSafeUserdata()
            );
            $this->load->view('templates/main', $data);
        }
        else redirect('logout');
    }
}