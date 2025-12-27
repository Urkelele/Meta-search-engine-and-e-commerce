<?php
include "../includes/header.php";
include "../includes/database.php";

$message = "";

// Check if token is provided
if (isset($_GET['token'])) {

    $token = $_GET['token'];

    // Find user with that token
    $stmt = $conn->prepare("SELECT id FROM users WHERE confirmation_token=?");
    $stmt->bind_param("s", $token);
    $stmt->execute();

    $result = $stmt->get_result();

    // If user found, confirm account
    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();
        $user_id = $user['id'];

        // Update user to confirmed
        $stmt = $conn->prepare("UPDATE users SET confirmed=1, confirmation_token=NULL WHERE id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $message = "Your account has been confirmed! You can now log in.";
    } else {
        $message = "Invalid or expired confirmation link.";
    }
} else {
    $message = "No token provided.";
}
?>

<h2>Email Confirmation</h2>
<p><?= $message ?></p>

<a href="login.php">Go to Login</a>

<?php include "../includes/footer.php"; ?>
