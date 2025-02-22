<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class responsible for managing the user table in the database.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 */
class AccountModel extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns the user data requires for authentication.
     *
     * @param string $email  email of the user
     * @return int|object      returns 0 if the user was not found, or their data if found
     */
    function GetUserData(string $email)
    {
        $sql = "SELECT id, password, role FROM user WHERE email = '$email'";
        $query = $this->db->query($sql);

        if (isset($query->row()->password) && $query->row()->password)
            return (object)$query->row();
        else return 0;
    }

    /**
     * Inserts a new user into the database.
     *
     * @param array $queryData  user to be inserted
     * @return int the local db id of the inserted user
     */
    function RegisterNewUser(array $queryData = []): int
    {
        $this->db->insert('user', $queryData);
        return $this->db->conn_id->insert_id;
    }

    /**
     * Checks whether an account with this email address exists.
     *
     * @param string $email  email to check
     * @return boolean      returns 0 if the email is not unique and 1 otherwise
     */
    function isEmailUnique(string $email): bool
    {
        $sql = "SELECT email FROM user WHERE email = '$email'";
        $query = $this->db->query($sql);

        if (isset($query->row()->email) && $query->row()->email) return false;
        else return true;
    }

    /**
     * Generates a password reset key.
     *
     * @return string      returns the password reset key
     * @author stack overflow (original author unknown)
     */
    function getToken(): string
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet .= "0123456789";
        $codeALength = strlen($codeAlphabet) - 1;

        for ($i = 0; $i < 255; ++$i) {
            $token .= $codeAlphabet[rand(0, $codeALength)];
        }

        return $token;
    }

    /**
     * Inserts a password reset key into the database.
     *
     * @param string $email  email of the user who resets their password
     * @return string      returns the password reset key
     */
    function insertPasswordUpdateLink(string $email): string
    {
        $keyToInsert = $this->getToken();
        $sql = "UPDATE user SET passwordResetKey = '$keyToInsert' WHERE email = '$email'";
        $this->db->simple_query($sql);

        return $keyToInsert;
    }

    /**
     * Mails the password reset link to the user.
     *
     * @param string $email  email of the user
     * @param string $resetKey  password reset key
     * @return void
     */
    function sendPasswordChangeEmail(string $email, string $resetKey)
    {
        $resetLink = base_url('forgottenPassword/reset?qs='.$resetKey);
        $subject = "Zresetuj hasło w RAPPAR";
        $headers = array(
            'From: "RAPPAR" <noreply@uberrapsy.pl>',
            'Reply-To: noreply@uberrapsy.pl',
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8'
        );
        $txt = "Otrzymaliśmy prośbę o zresetowanie hasła przypisanego do tego adresu email <br />
                na platformie RAPPAR. Jeżeli to Ty wysłałeś zgłoszenie, użyj poniższy link aby
                zresetować swoje hasło.<br /><br />
                Zresetuj hasło: <a href='$resetLink' target='_blank'>$resetLink</a><br /><br />
                Jeżeli to nie Ty wysłałeś zgłoszenie, zignoruj tę wiadomość.";

        mail($email, $subject, $txt, implode("\r\n", $headers));
    }

    /**
     * Checks whether the entered password reset key is valid.
     *
     * @param string $key  password reset key used
     * @return int|object      returns 0 if invalid or the user id when valid
     */
    function validatePasswordResetString(string $key)
    {
        $sql = "SELECT id FROM user WHERE passwordResetKey = '$key'";
        $query = $this->db->query($sql);

        if (isset($query->row()->id) && $query->row()->id) return $query->row()->id;
        else return 0;
    }

    /**
     * Updates user's password in the database
     *
     * @param string $password  new password entered by the user
     * @param string $userId  id of the user to update
     * @return void
     */
    function updateUserPassword(string $password, string $userId)
    {
        $newPass = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE user SET passwordResetKey = NULL, password = '$newPass' WHERE id = $userId";
        $this->db->simple_query($sql);
    }

    /**
     * Fetches user data and compares the input password with the real password
     * If the sign in is successful, a user session is set
     *
     * @param string $email
     * @param string $password
     * @return bool
     */
    function SignIn(string $email, string $password): bool
    {
        $userData = $this->GetUserData($email);
        $passwordToCompare = $userData->password ?? 0;

        if ($passwordToCompare && password_verify($password, $passwordToCompare))
        {
            $_SESSION['userLoggedIn'] = 1;
            $_SESSION['userRole'] = $userData->role;
            $_SESSION['userId'] = $userData->id;
            return true;
        }
        else return false;
    }

    /**
     * If the login cookie exists, the function signs the user in when vising the homepage
     * @return bool authentication status
     */
    function AutomaticSignIn(): bool
    {
        $data['email'] = json_decode($_COOKIE["login"])->userEmail;
        $data['password'] = json_decode($_COOKIE["login"])->userPassword;

        if (isset($data['email']) && $data['email'] && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->SignIn($data['email'], $data['password']);
        }
        else return false;
    }

    /**
     * Fetches the username of the user with the provided id number
     *
     * @param int $userId
     * @return string
     */
    function FetchUsernameById($userId): string
    {
        $sql = "SELECT username FROM user WHERE id = $userId";
        return $this->db->query($sql)->row()->username;
    }

    /**
     * Fetches all safe user data of all users to display in the users dashboard
     * Safe user data is: database id, username, role, accountLocked status
     *
     * @return array returns all found users' safe data
     */
    function fetchAllSafeUserdata(): array
    {
        $sql = "SELECT id, username, role, accountLocked FROM user";
        $query = $this->db->query($sql);

        return $query->result();
    }
}