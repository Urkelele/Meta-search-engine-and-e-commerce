<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function SendVerificationEmail(string $toEmail, string $token)
{
    $mail = new PHPMailer(true);
    try {
    // Configuration for the SMTP server
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'algarseeee04@gmail.com';
    $mail->Password   = 'dbqe mnsg qhao fxpx ';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('algarseeee04@gmail.com', 'Tech Shop');
    $mail->addAddress($toEmail);

    // Building of the URL of the token
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $verifyUrl = $protocol . '://' . $host . $path . '/index.php?page=verify&token=' . urlencode($token);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Email Validation for Tech Shop';
    $mail->Body    = '<h1>Thank you for registering in Tech Shop</h1> <p>Please confirm your email by clicking the link below:</p>
    <a href="' . htmlspecialchars($verifyUrl) . '">Verify My Account</a>
    <p>If you did not request this email, please ignore it.</p>';
    $mail->AltBody = 'Error in the printing of HTML';

    $mail->send();
    return true;

    } catch (Exception $e) {
        return false;
    }
}
function SendPasswordRecover(string $toEmail, string $token): bool
{
    $mail = new PHPMailer(true);
    try {
        // Configuration for the SMTP server
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'algarseeee04@gmail.com';
        $mail->Password   = 'dbqe mnsg qhao fxpx ';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('algarseeee04@gmail.com', 'Tech Shop');
        $mail->addAddress($toEmail);

        // Building of the URL of the token
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $resetUrl = $protocol . '://' . $host . $path . '/index.php?page=reset_password&token=' . urlencode($token);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body    = '<h2>Request of reseting password</h2>' .
                         '<p>We have received a request to change the password of your account. Click the link below to reset it:</p>' .
                         '<p><a href="' . htmlspecialchars($resetUrl) . '">Reset my password</a></p>' .
                         '<p>If you did not request this, please ignore this email.</p>' .
                         '<p>This link will expire in 1 hour.</p>';
        $mail->AltBody = 'Copy and paste this URL into your browser to reset your password: ' . $resetUrl . 'If you did not request this, please ignore this email.';

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>