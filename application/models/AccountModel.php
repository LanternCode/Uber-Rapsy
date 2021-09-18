<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AccountModel extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function GetUserData($email)
    {
        $sql = "SELECT id, password, role FROM user WHERE email = '$email'";
        $query = $this->db->query($sql);

        if (isset($query->row()->password) && $query->row()->password)
            return (object)$query->row();
        else return 0;
    }

    function RegisterNewUser($email, $password)
    {
        $sql = "INSERT INTO user
        ( email, password, role )
        VALUES
        ( '$email', '$password', 'user')";

        $this->db->simple_query($sql);
    }

    function IsEmailUnique($email)
    {
        $sql = "SELECT email FROM user WHERE email = '$email'";
        $query = $this->db->query($sql);

        if (isset($query->row()->email) && $query->row()->email) return 0;
        else return 1;
    }

    //SOURCE: STACK OVERFLOW
    function getToken()
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

    function InsertPasswordUpdateLink($email)
    {
        $keyToInsert = $this->getToken();
        $sql = "UPDATE user SET passwordResetKey = '$keyToInsert' WHERE email = '$email'";
        $this->db->simple_query($sql);

        return $keyToInsert;
    }

    function SendPasswordChangeEmail($email, $resetKey)
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

    function ValidatePasswordResetString($key)
    {
        $sql = "SELECT id FROM user WHERE passwordResetKey = '$key'";
        $query = $this->db->query($sql);

        if (isset($query->row()->id) && $query->row()->id) return $query->row()->id;
        else return 0;
    }

    function UpdateUserPassword($password, $userId)
    {
        $newPass = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE user SET passwordResetKey = NULL, password = '$newPass' WHERE id = $userId";
        $this->db->simple_query($sql);
    }
}