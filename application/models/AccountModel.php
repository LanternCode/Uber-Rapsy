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
     * Returns the URL of a playlist.
     *
     * @param string $email  email of the user
     * @return int|object      returns 0 if user not found or their data if found
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
     * Inserts a new user
     *
     * @param string $email  email of the user
     * @param string $password  hashed password of the user
     * @return void
     */
    function RegisterNewUser(string $email, string $password)
    {
        $sql = "INSERT INTO user
        ( email, password, role )
        VALUES
        ( '$email', '$password', 'user')";

        $this->db->simple_query($sql);
    }

    /**
     * Checks whether an account with this email address exists.
     *
     * @param string $email  email to check
     * @return boolean      returns 0 if email is not unique and 1 otherwise
     */
    function IsEmailUnique(string $email): bool
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
    function InsertPasswordUpdateLink(string $email): string
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
    function SendPasswordChangeEmail(string $email, string $resetKey)
    {
        $resetLink = base_url('forgottenPassword/reset?qs=' . $resetKey);
        $subject = "Zresetuj Hasło: Uber-Rapsy";
        $headers = array(
            'From: No reply',
            'Reply-To: noreply@UberRapsy.pl',
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=ISO-8859-1'
        );
        $txt = "It appears that someone has requested to change the password assigned to this
        email <br /> on the Uber Rapsy web application. If it was you, press the
        URL located below.<br /><br />Reset Password: <a href='$resetLink' target='_blank'>
        $resetLink</a><br /><br /> If you did not ask for a password reset, simply ignore this email.";

        mail($email, $subject, $txt, implode("\r\n", $headers));
    }

    /**
     * Checks whether the entered password reset key is valid.
     *
     * @param string $key  password reset key used
     * @return int|object      returns 0 if invalid or the user id when valid
     */
    function ValidatePasswordResetString(string $key)
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
    function UpdateUserPassword(string $password, string $userId)
    {
        $newPass = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE user SET passwordResetKey = NULL, password = '$newPass' WHERE id = $userId";
        $this->db->simple_query($sql);
    }
}