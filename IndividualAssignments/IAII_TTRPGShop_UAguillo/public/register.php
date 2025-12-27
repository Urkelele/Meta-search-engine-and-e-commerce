<?php
include "../includes/header.php";
include "../includes/database.php";

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "../includes/PHPMailer/src/PHPMailer.php";
require "../includes/PHPMailer/src/SMTP.php";
require "../includes/PHPMailer/src/Exception.php";

$success = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Hash the password
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    if ($stmt->get_result()->num_rows > 0) {
        $error = "Email already registered.";
    } else {
        // Generate confirmation token
        $token = bin2hex(random_bytes(16));

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (email, password_hash, confirmation_token) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $pass, $token);
        $stmt->execute();

        // Confirmation link
        $confirm_link = "http://localhost/PAPI/IAII_TTRPGShop_UAguillo/public/confirm.php?token=" . $token;

        // Send real email
        $mail = new PHPMailer(true);

        try {
            // Config SMTP
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = "urkoaguillourarte@gmail.com"; // Company email
            $mail->Password = "dtkn iefv umwt wbsb";  // App password

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // From
            $mail->setFrom("urkoaguillourarte@gmail.com", "TTRPG Shop");

            // To
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = "Confirm your account";
            $mail->Body = "
                <h2>Welcome to TTRPG Shop!</h2>
                <p>Click the following link to activate your account:</p>
                <p><a href='$confirm_link'>$confirm_link</a></p>
            ";
            //Non HTML alternative (Dont know if needed, but heard it's good practice)
            $mail->AltBody = "Click this link to activate your account: $confirm_link";
            $mail->send();

            echo "<p style='color:green;'>Confirmation email sent. Check your inbox.</p>";

        } catch (Exception $e) {
            echo "<p style='color:red;'>Error sending email: {$mail->ErrorInfo}</p>";
        }

        // Show confirmation link (for testing)
        // $success = "Account created! Click the following link to confirm your account:<br>
        // <a href='$confirm_link'>$confirm_link</a>";

    }
}
?>

<h2>Register</h2>

<?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
<?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

<!-- Form -->
<form method="post">
    Email:<br>
    <input type="email" name="email" required><br>
    Password:<br>
    <input type="password" name="password" required><br><br>
    <button type="submit">Register</button>
</form>

<?php include "../includes/footer.php"; ?>
