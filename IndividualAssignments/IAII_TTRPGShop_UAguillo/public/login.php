<?php
include "../includes/header.php";
include "../includes/database.php";

$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $pass = $_POST['password'];

    // Search user by email
    $stmt = $conn->prepare("SELECT id, password_hash, is_admin, confirmed FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    // If user found
    if ($res->num_rows == 1) {
        $user = $res->fetch_assoc();

        if (!$user['confirmed']) {
            $error = "Please confirm your email first.";
        } elseif (password_verify($pass, $user['password_hash'])) {

            // Start session and set variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_admin'] = $user['is_admin'];

            // Redirect based on role
            if ($_SESSION['user_admin'] == 1) {
                header("Location: ../admin/index.php");
                exit;
            } else {
                header("Location: index.php");
                exit;
            }
        } else {
            $error = "Invalid credentials."; // Wrong password
        }
    } else {
        $error = "User not found."; // No user with that email
    }
}
?>

<h2>Login</h2>

<?php if ($error): ?>
<p style="color:red;"><?= $error ?></p>
<?php endif; ?>

<!-- Login form -->
<form method="post">
    Email:<br>
    <input type="email" name="email" required><br>
    Password:<br>
    <input type="password" name="password" required><br><br>
    <button type="submit">Login</button>
</form>
<p><a href="reset_request.php">Forgot your password?</a></p>

<?php include "../includes/footer.php"; ?>
