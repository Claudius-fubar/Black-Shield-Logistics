<?php
// mailer.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer classes manually since we don't have Composer
require_once __DIR__ . '/PHPmailer/Exception.php';
require_once __DIR__ . '/PHPmailer/PHPMailer.php';
require_once __DIR__ . '/PHPmailer/SMTP.php';

/**
 * send_mail_smtp
 * @param string $to
 * @param string $subject
 * @param string $body_html
 * @param string $body_text
 * @return bool
 */
function send_mail_smtp($to, $subject, $body_html, $body_text = '') {
    // Citire config din ENV (sau înlocuiește cu stringuri în dev)
    $smtpHost = getenv('SMTP_HOST') ?: 'smtp.sendgrid.net';
    $smtpUser = getenv('SMTP_USER') ?: 'apikey';       // SendGrid uses 'apikey' as user
    $smtpPass = getenv('SMTP_PASS') ?: 'SENDGRID_API_KEY';
    $smtpPort = getenv('SMTP_PORT') ?: 587;
    $smtpSecure = getenv('SMTP_SECURE') ?: 'tls';      // 'tls' sau 'ssl'
    $fromEmail = getenv('MAIL_FROM') ?: 'no-reply@yourdomain.example';
    $fromName = getenv('MAIL_FROM_NAME') ?: 'Black Shield';

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUser;
        $mail->Password = $smtpPass;
        $mail->SMTPSecure = $smtpSecure;
        $mail->Port = (int)$smtpPort;

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body_html;
        $mail->AltBody = $body_text ?: strip_tags($body_html);

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log error in production: $mail->ErrorInfo
        error_log("Mailer error: " . $mail->ErrorInfo);
        return false;
    }
}
