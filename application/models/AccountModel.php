<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model responsible for managing the User database table.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 */
class AccountModel extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns the user data requires for authentication.
     *
     * @param string $email user email
     * @return int|object returns 0 if the user was not found, or their data if found
     */
    public function getUserData(string $email): int|object
    {
        $sql = "SELECT id, password, role FROM user WHERE email = '$email'";
        $query = $this->db->query($sql);

        if (isset($query->row()->password) && $query->row()->password)
            return $query->row();
        else return 0;
    }

    /**
     * Insert a new user into the database.
     *
     * @param array $queryData new account details
     * @return int id of the newly inserted user
     */
    public function registerNewUser(array $queryData): int
    {
        $this->db->insert('user', $queryData);
        return $this->db->conn_id->insert_id;
    }

    /**
     * Check whether an account with this email address exists.
     *
     * @param string $email
     * @return bool true if no such account exists, false otherwise
     */
    public function isEmailUnique(string $email): bool
    {
        $sql = "SELECT email FROM user WHERE email = '$email'";
        $query = $this->db->query($sql);

        if (isset($query->row()->email) && $query->row()->email)
            return false;
        else return true;
    }

    /**
     * Generate a password reset key.
     *
     * @return string
     * @author Stack Overflow (original author unknown)
     */
    public function getToken(): string
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
     * Attach a password reset key to the user.
     *
     * @param string $email user's email address
     * @return string the password reset key
     */
    public function insertPasswordUpdateLink(string $email): string
    {
        $keyToInsert = $this->getToken();
        $sql = "UPDATE user SET passwordResetKey = '$keyToInsert' WHERE email = '$email'";
        $this->db->simple_query($sql);

        return $keyToInsert;
    }

    /**
     * Mail the password reset link to the user.
     *
     * @param string $email
     * @param string $resetKey
     * @return void
     */
    public function sendPasswordChangeEmail(string $email, string $resetKey): void
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
                na platformie RAPPAR. Skorzystaj z poniższego linku by zresetować swoje
                hasło lub zignoruj tę wiadomość.<br /><br />
                Zresetuj hasło: <a href='$resetLink' target='_blank'>$resetLink</a><br /><br />.";

        mail($email, $subject, $txt, implode("\r\n", $headers));
    }

    /**
     * Check whether the entered password reset key is valid.
     *
     * @param string $key
     * @return int|object returns the id of the matching user's password reset key, or 0 if no matching user was found
     */
    public function validatePasswordResetString(string $key): int|object
    {
        $sql = "SELECT id FROM user WHERE passwordResetKey = '$key'";
        $query = $this->db->query($sql);

        if (isset($query->row()->id) && $query->row()->id)
            return $query->row()->id;
        else return 0;
    }

    /**
     * Update the user's password.
     *
     * @param string $password the new password entered by the user
     * @param string $userId
     * @return void
     */
    public function updateUserPassword(string $password, string $userId): void
    {
        $newPass = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE user SET passwordResetKey = NULL, password = '$newPass' WHERE id = $userId";
        $this->db->simple_query($sql);
    }

    /**
     * Fetch user credentials and compare the inputted password with the actual password.
     * Set a user session upon a successful sign in is.
     *
     * @param string $email
     * @param string $password
     * @return bool true if the sign in was successful, false otherwise
     */
    public function signIn(string $email, string $password): bool
    {
        $userData = $this->getUserData($email);
        $passwordToCompare = $userData->password ?? 0;

        if ($passwordToCompare && password_verify($password, $passwordToCompare)) {
            $_SESSION['userLoggedIn'] = 1;
            $_SESSION['userRole'] = $userData->role;
            $_SESSION['userId'] = $userData->id;
            return true;
        }
        else return false;
    }

    /**
     * Automatically sign the user in upon visiting the homepage if a valid login cookie exists.
     *
     * @return bool true if the sign in was successful, false otherwise
     */
    public function automaticSignIn(): bool
    {
        $data['email'] = json_decode($_COOKIE["login"])->userEmail;
        $data['password'] = json_decode($_COOKIE["login"])->userPassword;

        if (isset($data['email']) && $data['email'] && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->signIn($data['email'], $data['password']);
        }
        else return false;
    }

    /**
     * Fetch the user's username.
     *
     * @param int $userId
     * @return string
     */
    public function fetchUsernameById(int $userId): string
    {
        $sql = "SELECT username FROM user WHERE id = $userId";
        return $this->db->query($sql)->row()->username;
    }

    /**
     * Fetch all safe user data of every user on the platform.
     * This data is displayed in a dashboard only visible to RAPPAR staff.
     * Safe user data includes: user id in the database, username, role, accountLocked status.
     *
     * @return array
     */
    public function fetchAllSafeUserdata(): array
    {
        $sql = "SELECT id, username, role, accountLocked FROM user";
        $query = $this->db->query($sql);

        return $query->result();
    }
}