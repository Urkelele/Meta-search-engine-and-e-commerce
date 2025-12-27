<?php
include "../includes/header.php";
include "../includes/database.php";

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "../includes/PHPMailer/src/PHPMailer.php";
require "../includes/PHPMailer/src/SMTP.php";
require "../includes/PHPMailer/src/Exception.php";

$message = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        // Generate reset token
        $token = bin2hex(random_bytes(16));

        // Store token in DB
        $stmt = $conn->prepare("UPDATE users SET reset_token=? WHERE email=?");
        $stmt->bind_param("ss", $token, $email);
        $stmt->execute();
        // Create reset link
        $reset_link = "http://localhost/PAPI/IAII_TTRPGShop_UAguillo/public/reset_password.php?token=$token";

        // Option 1 — Send reset email
        $mail = new PHPMailer(true);

        try {
            // Config SMTP
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = "urkoaguillourarte@gmail.com";
            $mail->Password = "dtkn iefv umwt wbsb";  // App password

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // From
            $mail->setFrom("urkoaguillourarte@gmail.com", "TTRPG Shop");

            // To
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = "Password Reset Request";
            $mail->Body = "
                <h2>Welcome to TTRPG Shop!</h2>
                <p>Click the following link to reset your password:</p>
                <p><a href='$reset_link'>$reset_link</a></p>
            ";
            //Non HTML alternative
            $mail->AltBody = "Click the following link to reset your password: $reset_link";
            $mail->send();

            echo "<p style='color:green;'>Reset email sent. Check your inbox.</p>";

        } catch (Exception $e) {
            echo "<p style='color:red;'>Error sending email: {$mail->ErrorInfo}</p>";
        }

        // Option 2 — Show reset link directly (for testing)
        // $message = "A password reset email has been sent.<br>
        // <a href='$reset_link'>$reset_link</a>";

    } else {
        $error = "No account found with that email.";
    }
}
?>

<h2>Forgot Password</h2>

<?php if ($message): ?><p style="color:green;"><?= $message ?></p><?php endif; ?>
<?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>

<form method="post">
    Enter your email:<br>
    <input type="email" name="email" required><br><br>
    <button type="submit">Send reset email</button>
</form>

<?php include "../includes/footer.php"; ?>
