<?php defined('BASEPATH') or exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * A service responsible for email comms.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/RAPPAR/
 */
class MailService
{
    /** @var array */
    protected $cfg;

    public function __construct()
    {
        //Load secrets from application/api/smtp.json
        $path = APPPATH . 'api/smtp.json';
        $json = @file_get_contents($path);
        if ($json === false) {
            log_message('error', "MailService: cannot read $path");
            throw new \RuntimeException('Email configuration unavailable');
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            log_message('error', "MailService: invalid JSON in $path");
            throw new \RuntimeException('Email configuration invalid');
        }

        //Required keys
        foreach (['host', 'port', 'username', 'password', 'from_email', 'from_name'] as $k) {
            if (empty($data[$k])) {
                log_message('error', "MailService: missing key '$k' in $path");
                throw new \RuntimeException("Email configuration missing '$k'");
            }
        }

        //Defaults
        $data['reply_to'] = $data['reply_to'] ?? $data['from_email'];
        $data['encryption'] = $data['encryption'] ?? 'tls'; // tls=STARTTLS:587, ssl=SMTPS:465
        $data['bounce'] = $data['bounce'] ?? null;

        $this->cfg = $data;
    }

    /**
     * Send a password reset email.
     *
     * @param string $username
     * @param string $toEmail user's email address
     * @param string $resetLink the password reset link
     * @return bool whether the email was sent successfully
     */
    public function sendPasswordReset(string $username, string $toEmail, string $resetLink): bool
    {
        //Build email body
        $text = "Cześć {$username},\r\n"
                . "\r\nOtrzymaliśmy prośbę o zresetowanie hasła przypisanego do tego adresu e-mail na platformie RAPPAR.\r\n"
                . "\r\nAby zresetować hasło, kliknij w ten link:\r\n"
                . "{$resetLink}\r\n"
                . "\r\nJeśli to nie Ty zainicjowałeś prośbę, zignoruj tę wiadomość.\r\n"
                . "\r\nPozdrawiamy,\r\n"
                . "Zespół RAPPAR";

        return $this->sendSimple($toEmail, 'Zresetuj hasło w RAPPAR.', $text);
    }

    /**
     * Send a password reset email.
     *
     * @param string $username
     * @param string $toEmail user's email address
     * @param int $newAccStatus 1 (blocked) or 0 (active)
     * @param string $reason reason for the change
     * @return bool whether the email was sent successfully
     */
    public function sendAccountStatusChangeEmail(string $username, string $toEmail, int $newAccStatus, string $reason): bool
    {
        //Build email body
        $statusText = $newAccStatus ? 'zablokowane' : 'odblokowane';
        $text = "Cześć {$username},\r\n"
                . "\r\nTwoje konto na platformie RAPPAR zostało {$statusText} z następującej przyczyny:\r\n"
                . "{$reason}\r\n"
                . "\r\nJeśli uważasz, że zmiana statusu konta jest nieuzasadniona, skontaktuj się z nami odpowiadając na tę wiadomość.\r\n"
                . "\r\nPozdrawiamy,\r\n"
                . "Zespół RAPPAR";

        return $this->sendSimple($toEmail, 'Zmiana statusu konta w RAPPAR.', $text);
    }

    /**
     * General-purpose sender used by the reset method.
     *
     * @return bool whether the email was sent successfully
     */
    public function sendSimple(string $toEmail, string $subject, string $text): bool
    {
        $mail = new PHPMailer(true);

        try {
            //Transport
            $mail->isSMTP();
            $mail->Host = $this->cfg['host'];
            $mail->Port = (int)$this->cfg['port'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->cfg['username'];
            $mail->Password = $this->cfg['password'];
            $mail->CharSet = 'UTF-8';
            $mail->SMTPSecure = ($this->cfg['encryption'] === 'ssl')
                ? PHPMailer::ENCRYPTION_SMTPS      // port 465
                : PHPMailer::ENCRYPTION_STARTTLS;  // port 587

            //Headers
            $mail->setFrom($this->cfg['from_email'], $this->cfg['from_name']);
            $mail->addReplyTo($this->cfg['reply_to'], $this->cfg['from_name']);
            $mail->addAddress($toEmail);
            if (!empty($this->cfg['bounce'])) {
                $mail->Sender = $this->cfg['bounce']; // envelope sender for bounces
            }

            //Content
            $mail->Subject = $subject;
            $mail->isHTML(false);
            $mail->Encoding = 'quoted-printable';
            $mail->WordWrap = 0;
            $mail->Body = $text;

            $mail->send();
            return true;
        }
        catch (Exception $e) {
            //PHPMailer explains exactly what failed (auth, TLS, etc.)
            log_message('error', 'MailService send error: ' . $mail->ErrorInfo);
            return false;
        }
    }
}
