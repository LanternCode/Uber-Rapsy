<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!isset($_SESSION))
    session_start();

/**
 * Controller responsible for handling views related to accounts.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/RAPPAR/
 *
 * @property PlaylistModel $PlaylistModel
 * @property AccountModel $AccountModel
 * @property SecurityModel $SecurityModel
 * @property LogModel $LogModel
 * @property CI_DB_mysqli_driver $db
 * @property CI_Input $input
 * @property HTMLSanitiser $htmlsanitiser
 * @property MailService $MailService
 */
class Account extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('AccountModel');
        $this->load->model('PlaylistModel');
        $this->load->library('MailService');
        $this->MailService = new MailService();
        $this->load->helper('cookie');
    }

    /**
     * Open and processes the login form.
     *
     * @return void
     */
    public function login(): void
    {
        //If the user is already logged in, restart their session
        $data['userLoggedIn'] = $this->SecurityModel->authenticateUser();
        if ($data['userLoggedIn'])
            redirect('logout');

        //Fetch the credentials from the form or the pre-set cookie
        $enteredEmail = isset($_COOKIE["login"]) ? json_decode($_COOKIE["login"])->userEmail : $this->input->post('userEmail');
        $enteredPassword = isset($_COOKIE["login"]) ? json_decode($_COOKIE["login"])->userPassword : $this->input->post('userPassword');
        $data['redirectSource'] = $this->input->get('src') ?? '';

        //Only attempt the login if the form was submitted or the cookie is set
        $data['body'] = 'login';
        if (filter_var($enteredEmail, FILTER_VALIDATE_EMAIL)) {
            $accountData = $this->AccountModel->getUserData($enteredEmail);
            $loginSuccessful = $this->AccountModel->signIn($enteredPassword, $accountData->password);
            if ($loginSuccessful) {
                //Check if the user is banned
                if ($accountData->accountLocked)
                    $data['body'] = 'account/accountLocked';
                else {
                    //Establish a new user session
                    $this->AccountModel->establishUserSession($accountData);

                    //Save the session if the user pressed the 'do not logout' button
                    $doNotLogout = $this->input->post('doNotLogout');
                    if ($doNotLogout) {
                        $loginSessionDetails = array(
                            'userEmail' => $enteredEmail,
                            'userPassword' => $enteredPassword
                        );
                        setcookie("login", json_encode($loginSessionDetails), time() + (86400 * 14), "/");
                    }

                    redirect(base_url($data['redirectSource']));
                }
            }
            else
                $data['invalidCredentials'] = 1;
        }

        $this->load->view('templates/main', $data);
    }

    /**
     * Handle new account registrations.
     *
     * @return void
     */
    public function newAccount(): void
    {
        //If the user is already logged in, reset the session
        $userLoggedIn = $this->SecurityModel->authenticateUser();
        if ($userLoggedIn)
            redirect('logout');

        //Process the form if it was submitted
        $template = 'templates/main';
        $data['body'] = 'account/register';
        $data['redirectSource'] = $this->input->get('src') ?? '';
        if ($this->input->post()) {
            //Fetch form data
            $username		= $this->input->post('register--username');
            $email			= $this->input->post('register--email');
            $password		= $this->input->post('register--password');
            $passwordRep 	= $this->input->post('register--password__repetition');
            $termsOfService	= $this->input->post('register--TOS');

            //Username filters
            $data['usernameTooLong']  = strlen($username) > 20 ? "Nazwa użytkownika nie może być dłuższa niż 20 znaków." : 0;
            $username = $this->htmlsanitiser->purify($username);
            $data['usernameTooShort'] = strlen($username) > 0  ? 0 : "Nazwa użytkownika jest wymagana!";

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

            //Validate form inputs
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
                $template = 'templates/song';
                $data['body'] = 'account/registrationSuccessful';
                $data['userHasRegistered'] = 1;

                //Collect the required account information
                $queryData['username'] = $username;
                $queryData['email'] = $email;
                $queryData['password'] = password_hash($password, PASSWORD_BCRYPT);;
                $queryData['role'] = "user";

                //Create account
                $this->AccountModel->registerNewUser($queryData);

                //Automatically sign the user in for 14 days
                session_unset();
                session_destroy();
                $authSuccess = $this->AccountModel->signIn($email, $password);
                $loginSessionDetails = array(
                    'userEmail' => $email,
                    'userPassword' => $password
                );
                setcookie("login", json_encode($loginSessionDetails), time() + (86400 * 14), "/");
            }
        }

        $this->load->view($template, $data);
    }

    /**
     * Handle logouts.
     *
     * @return void
     */
    public function logout(): void
    {
        //Delete the user session
        session_unset();
        session_destroy();

        //Delete the 'do not sign me out' cookie
        if (isset($_COOKIE['login']))
            unset($_COOKIE['login']);

        //Override the login cookie with an expired one
        setcookie("login", "", time() - 3600, "/");

        $data['body'] = "logout";
        $this->load->view('templates/main', $data);
    }

    /**
     * Handle the forgotten password form.
     *
     * @return void
     */
    public function forgottenPassword(): void
    {
        //If the user is already logged in, reset the session
        $userLoggedIn = $this->SecurityModel->authenticateUser();
        if ($userLoggedIn)
            redirect('logout');

        $data = array(
            'title' => 'Przypomnij hasło | Wypełnij formularz a dalsze instrukcje zostaną wysłane na podany adres email',
            'body' => 'account/forgotPassword'
        );

        //Verify the provided email address
        $enteredEmail = $this->input->post('email');
        $enteredEmail = filter_var($enteredEmail, FILTER_VALIDATE_EMAIL);
        if ($enteredEmail) {
            if (!$this->AccountModel->isEmailUnique($enteredEmail)) {
                $username = $this->AccountModel->getUserData($enteredEmail)->username;
                $resetKey = $this->AccountModel->insertPasswordUpdateLink($enteredEmail);
                if ($resetKey) {
                    $resetLink = base_url('forgottenPassword/reset?qs='.$resetKey);
                    $this->MailService->sendPasswordReset($username, $enteredEmail, $resetLink);
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
     * Handle the password reset form.
     * This form is accessed through the link emailed to the user.
     *
     * @return void
     */
    public function resetPassword(): void
    {
        //If the user is already logged in, reset the session
        $userLoggedIn = $this->SecurityModel->authenticateUser();
        if ($userLoggedIn)
            redirect('logout');

        $data = array(
            'title' => 'Reset hasła | Wprowadź nowe hasło aby dokończyć proces resetowania hasła',
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
     * Open the users dashboard (with only safe data presented).
     *
     * @return void
     */
    public function usersDashboard(): void
    {
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();
        if ($userAuthenticated) {
            $data = array(
                'body' => 'admin/usersDashboard',
                'title' => 'Panel zarządzania użytkownikami',
                'users' => $this->AccountModel->fetchAllSafeUserdata(),
                'userLoggedIn' => true,
                'isReviewer' => true
            );
            $this->load->view('templates/main', $data);
        }
        else redirect('errors/403-404');
    }

    /**
     * Open the contributors' leaderboard.
     *
     * @return void
     */
    public function contributorsRanking(): void
    {
        $data = array(
            'title' => 'Ranking najbardziej aktywnych użytkowników | dołącz do nich oceniając, recenzując i dodając utwory!',
            'body' => 'account/topContributors',
            'ranking' => $this->AccountModel->getTopRapparContributors(),
            'userLoggedIn' => $this->SecurityModel->authenticateUser(),
            'isReviewer' => $this->SecurityModel->authenticateReviewer()
        );

        $this->load->view('templates/main', $data);
    }

    /**
     * Show a user's profile.
     *
     * @return void
     */
    public function userProfile(): void
    {
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();
        if (!$userAuthenticated)
            redirect('errors/403-404');

        $userId = $this->input->get('uid');
        $user = is_null($userId) ? false : $this->AccountModel->getUserProfile($userId);
        if (empty($user))
            redirect('errors/403-404');

        $data = array(
            'body' => 'account/userProfile',
            'title' => 'Profil użytkownika '.$user->username,
            'playlists' => $this->PlaylistModel->fetchUserPlaylists($userId),
            'profile' => $user,
            'scores' => $this->AccountModel->getUserPositionInRanking($userId),
            'logs' => $this->LogModel->getUserLogs($userId),
            'userId' => $userId,
            'userLoggedIn' => true,
            'isReviewer' => true
        );

        $this->load->view('templates/main', $data);
    }

    /**
     * Lock or unblock an account.
     *
     * @return void
     */
    public function changeStatus(): void
    {
        $userAuthenticated = $this->SecurityModel->authenticateReviewer();
        if (!$userAuthenticated)
            redirect('errors/403-404');

        $userId = $this->input->get('uid');
        $user = is_null($userId) ? false : $this->AccountModel->getUserProfile($userId);
        if (empty($user))
            redirect('errors/403-404');

        $data = array(
            'body' => 'account/switchAccountStatus',
            'title' => 'Profil użytkownika '.$user->username.' | Zmień status konta',
            'profile' => $user,
            'logs' => $this->LogModel->getUserLogs($userId),
            'userId' => $userId,
            'userLoggedIn' => true,
            'isReviewer' => true
        );

        if ($this->input->post()) {
            $confirmation = $this->input->post('conf');
            if (!empty($confirmation)) {
                $changeReason = $this->input->post('statusReason');
                if (!empty($changeReason)) {
                    $newAccountStatus = (int) !$data['profile']->accountLocked;
                    $this->AccountModel->updateUserAccountStatus($newAccountStatus, $userId);
                    $this->LogModel->createLog('user', $userId,
                        'Konto użytkownika zostało '.($newAccountStatus ? 'zablokowane' : 'odblokowane').' z powodu: '.$changeReason);
                    $this->MailService->sendAccountStatusChangeEmail($user->username, $user->email, $newAccountStatus, $changeReason);
                    redirect('user/changeAccountStatus?uid='.$userId);
                }
            }
        }

        $this->load->view('templates/main', $data);
    }
}