<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!isset($_SESSION))
    session_start();

/**
 * Controller responsible for handling views related to accounts.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 *
 * @property PlaylistModel $PlaylistModel
 * @property AccountModel $AccountModel
 * @property SecurityModel $SecurityModel
 * @property CI_DB_mysqli_driver $db
 * @property CI_Input $input
 */
class Account extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('AccountModel');
        $this->load->model('PlaylistModel');
        $this->load->model('SecurityModel');
        $this->load->helper('cookie');
    }

    /**
     * Opens and processes the login form
     *
     * @return void
     */
    public function login(): void
    {
        //If the user is already logged in, reset the session
        $userLoggedIn = $_SESSION['userLoggedIn'] ?? false;
        if ($userLoggedIn)
            redirect('logout');

        //Fetch the credentials from the form or the pre-set cookie
        $email = isset($_COOKIE["login"]) ? json_decode($_COOKIE["login"])->userEmail : $this->input->post('userEmail');
        $password = isset($_COOKIE["login"]) ? json_decode($_COOKIE["login"])->userPassword : $this->input->post('userPassword');

        //Only attempt the login if the form was submitted or the cookie is set
        if (filter_var($email ?? '', FILTER_VALIDATE_EMAIL)) {
            $loginSuccess = $this->AccountModel->SignIn($email, $password);
            if ($loginSuccess) {
                //Save the session if the user pressed the 'do not logout' button
                $doNotLogout = $this->input->post('doNotLogout');
                if ($doNotLogout) {
                    $loginSessionDetails = array(
                        'userEmail' => $email,
                        'userPassword' => $password
                    );
                    setcookie("login", json_encode($loginSessionDetails), time() + (86400 * 14), "/");
                }

                redirect(base_url());
            }
            else
                $data['invalidCredentials'] = 1;
        }

        $data['body'] = 'login';
        $this->load->view('templates/main', $data);
    }

    /**
     * Handles new account registrations
     *
     * @return void
     */
    public function newAccount(): void
    {
        //If the user is already logged in, reset the session
        $userLoggedIn = $_SESSION['userLoggedIn'] ?? false;
        if ($userLoggedIn)
            redirect('logout');

        //Process the form if it was submitted, otherwise just show the form
        $data['body'] = 'register';
        $formSubmitted = $this->input->post('formSubmitted');
        if ($formSubmitted) {
            //Fetch form data
            $username		= $this->input->post('register--username');
            $email			= $this->input->post('register--email');
            $password		= $this->input->post('register--password');
            $passwordRep 	= $this->input->post('register--password__repetition');
            $termsOfService	= $this->input->post('register--TOS');

            //Username filters
            $data['usernameTooShort'] = strlen($username) > 0  ? 0 : "Nazwa użytkownika jest wymagana!";
            $data['usernameTooLong']  = strlen($username) > 20 ? "Nazwa użytkownika nie może być dłuższa niż 20 znaków." : 0;

            //Email filters
            $data['emailFormatInvalid'] = filter_var($email, FILTER_VALIDATE_EMAIL) ? 0 : "Adres email jest wymagany!";
            $data['emailTooLong'] 		= strlen($email) > 50 ? "Adres email nie może być dłuższy niż 50 znaków." : 0;
            $data['emailRepeated'] 		= $this->AccountModel->isEmailUnique($email) ? 0 : "Istnieje już konto zarejestrowane na ten adres email. Jeżeli jest Twoje, wróć do ekranu logowania i wciśnij 'przypomnij hasło'.";

            //Password filters
            $data['passwordTooShort'] = strlen($password) > 3 ? 0 : "Hasło musi zawierać przynajmniej 4 znaki!";
            $data['passwordTooLong']  = strlen($password) > 25 ? "Hasło nie może być dłuższe niż 25 znaków." : 0;
            $data['passwordRepetitionNotMatching'] = $password == $passwordRep ? 0 : "Wpisane hasła nie są identyczne!";

            //ToS filter
            $data['termsOfServiceDenied'] = !$termsOfService ? "Aby kontynuować musisz zaakceptować zasady korzystania z serwisu." : 0;

            //Validate the form inputs
            if ($data['usernameTooShort'] || $data['usernameTooLong'] ||
                $data['emailFormatInvalid'] || $data['emailTooLong'] || $data['emailRepeated'] ||
                $data['passwordTooShort'] || $data['passwordTooLong'] ||
                $data['passwordRepetitionNotMatching'] ||
                $data['termsOfServiceDenied'])
            {
                //Construct the feedback message, exclude the array 'body' index
                $dataKeys = array_keys($data);
                for ($i = 0; $i < count($dataKeys); ++$i) {
                    if (!$data[$dataKeys[$i]])
                        $data[$dataKeys[$i]] = "";
                    elseif ($data[$dataKeys[$i]] != "register") {
                        $addInFront = "<h4 class='registrationError'>";
                        $addInFront .= $data[$dataKeys[$i]];
                        $addInFront .= "</h4>";
                        $data[$dataKeys[$i]] = $addInFront;
                    }
                }

                //Refill the correct form fields in case the form was not validated
                $data['setUsername']      	   = ($data['usernameTooShort'] || $data['usernameTooLong']) ? "" : $username;
                $data['setEmail']			   = ($data['emailFormatInvalid'] || $data['emailTooLong'] || $data['emailRepeated']) ? "" : $email;
                $data['setPassword']	  	   = ($data['passwordTooShort'] || $data['passwordTooLong']) ? "" : $password;
                $data['setPasswordRepetition'] = ($data['setPassword'] && !$data['passwordRepetitionNotMatching']) ? $password : "";
                $data['setTOS']                = $data['termsOfServiceDenied'] ? "" : "checked";
            }
            else {
                //Success view and message
                $data['body'] = 'registrationSuccessful';
                $data['userHasRegistered'] = 1;

                //Collect the required account information
                $queryData['username'] = $email;
                $queryData['email'] = $email;
                $queryData['password'] = password_hash($password, PASSWORD_BCRYPT);;
                $queryData['role'] = "user";

                //Create account
                $this->AccountModel->registerNewUser($queryData);

                //Automatically sign the user in for 7 days after registration
                session_unset();
                session_destroy();
                $authSuccess = $this->AccountModel->SignIn($email, $password);
                $loginSessionDetails = array(
                    'userEmail' => $email,
                    'userPassword' => $password
                );
                setcookie("login", json_encode($loginSessionDetails), time() + (86400 * 14), "/");
            }
        }

        $this->load->view('templates/main', $data);
    }

    /**
     * Handles logouts.
     *
     * @return void
     */
    public function logout(): void
    {
        //Delete the user session
        session_unset();
        session_destroy();

        //Delete the 'do not sign me out' cookie
        if (isset($_COOKIE['login'])) {
            unset($_COOKIE['login']);
        }

        //Override the login cookie with an expired one
        setcookie("login", "", time() - 3600, "/");

        $data['body'] = "logout";
        $this->load->view('templates/main', $data);
    }

    /**
     * Handles the forgotten password form.
     *
     * @return void
     */
    public function forgottenPassword(): void
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
        $enteredEmail = $this->input->post('email');
        $enteredEmail = filter_var($enteredEmail, FILTER_VALIDATE_EMAIL);
        if ($enteredEmail) {
            if (!$this->AccountModel->isEmailUnique($enteredEmail)) {
                $resetKey = $this->AccountModel->insertPasswordUpdateLink($enteredEmail);
                if ($resetKey) {
                    $this->AccountModel->sendPasswordChangeEmail($enteredEmail, $resetKey);
                    $data['actionNotification'] = "<span>Jeżeli istnieje konto założone na ten adres email, została na niego wysłana wiadomość z linkiem resetującym hasło.</span>";
                }
                else
                    $data['actionNotification'] = "Nie udało się wysłać linku resetującego hasło. Spróbuj ponownie później bądź skontaktuj się z administracją RAPPAR.";
            }
            else
                $data['actionNotification'] = "<span>Jeżeli istnieje konto założone na ten adres email, została na niego wysłana wiadomość z linkiem resetującym hasło.</span>";
        }
        elseif (isset($_POST['email']))
            $data['actionNotification'] = "Wpisano niepoprawny adres email.";

        $this->load->view('templates/main', $data);
    }

    /**
     * Handles the password reset form.
     * This form is accessed through the link emailed to the user.
     *
     * @return void
     */
    public function resetPassword(): void
    {
        //If the user is already logged in, reset the session
        $userLoggedIn = $_SESSION['userLoggedIn'] ?? false;
        if ($userLoggedIn)
            redirect('logout');

        $data = array(
            'title' => 'Zresetuj Hasło | Uber Rapsy',
            'body' => 'account/resetPassword',
            'errorMessage' => '',
            'key' => $this->input->get('qs')
        );

        //Check if a valid password reset key was provided
        $userId = $this->AccountModel->validatePasswordResetString($data['key']);
        if ($userId) {
            $password = $this->input->post('newPassword');
            $passwordRepeat = $this->input->post('newPasswordRepeated');

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
                elseif ($password != $passwordRepeat)
                    $data['errorMessage'] = "Wpisane hasła nie są identyczne.";

                $data['errorMessage'] .= "<br />";
            }
        }
        else {
            $data['body'] = 'account/resetPasswordResult';
            $data['result'] = "Podany link jest niepoprawny. Spróbuj jeszcze raz lub ponownie kliknij przycisk przypomnij hasło w panelu logowania i postępuj zgodnie z instrukcjami.";
        }

        $this->load->view('templates/main', $data);
    }

    /**
     * Opens the users dashboard (with only safe data presented).
     *
     * @return void
     */
    public function usersDashboard(): void
    {
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();
        if ($userAuthenticated) {
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