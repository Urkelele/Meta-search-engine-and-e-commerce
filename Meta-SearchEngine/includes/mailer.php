<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/../PHPMailer/src/Exception.php";
require __DIR__ . "/../PHPMailer/src/PHPMailer.php";
require __DIR__ . "/../PHPMailer/src/SMTP.php";

function sendVerificationEmailMSE(string $toEmail, string $token): bool {
  $mail = new PHPMailer(true);

  try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'algarseeee04@gmail.com';
    $mail->Password   = 'dbqe mnsg qhao fxpx ';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('algarseeee04@gmail.com', 'Meta Search Shop');
    $mail->addAddress($toEmail);

    // Construir URL a /public/verify.php?token=...
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // SCRIPT_NAME: /.../api/auth/register.php -> queremos llegar a /public/verify.php
    // Quitamos /api/auth/register.php:
    $basePath = preg_replace('~/api/auth/register\.php$~', '', $_SERVER['SCRIPT_NAME']);
    // $basePath ahora apunta a la raíz del proyecto (donde cuelgan /public, /api, etc.)
    $verifyUrl = $protocol . '://' . $host . $basePath . '/public/verify.php?token=' . urlencode($token);

    $mail->isHTML(true);
    $mail->Subject = 'Verify your account - Meta Search Shop';
    $mail->Body =
      '<h2>Welcome to Meta Search Shop</h2>' .
      '<p>Please verify your email by clicking the link:</p>' .
      '<p><a href="' . htmlspecialchars($verifyUrl) . '">Verify my account</a></p>' .
      '<p>This link expires in 24 hours.</p>';

    $mail->AltBody = "Verify your account: " . $verifyUrl;

    $mail->send();
    return true;
  } catch (Exception $e) {
    return false;
  }
}
