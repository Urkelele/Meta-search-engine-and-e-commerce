<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer/src/Exception.php';
require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';

function mse_base_url(): string {
  $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
  $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

  // si tu public está en /Meta-search-engine-and-e-commerce/public
  // ajusta esto si tu estructura es distinta:
  $path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); // /.../public
  return $protocol . '://' . $host . $path;
}

function SendVerificationEmailMSE(string $toEmail, string $token): bool {
  $mail = new PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'TUEMAIL@gmail.com';
    $mail->Password   = 'TU_APP_PASSWORD';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('TUEMAIL@gmail.com', 'MSE Platform');
    $mail->addAddress($toEmail);

    $verifyUrl = mse_base_url() . '/verify.php?token=' . urlencode($token);

    $mail->isHTML(true);
    $mail->Subject = 'Verify your email (MSE)';
    $mail->Body = '
      <h2>Welcome to Meta Search Platform</h2>
      <p>Click to verify your account:</p>
      <p><a href="'.htmlspecialchars($verifyUrl).'">Verify My Account</a></p>
      <p>If you didn’t register, ignore this email.</p>
    ';
    $mail->AltBody = "Verify: " . $verifyUrl;

    $mail->send();
    return true;
  } catch (Exception $e) {
    return false;
  }
}
