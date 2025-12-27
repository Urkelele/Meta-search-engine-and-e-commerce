<?php
include "../includes/header.php";
include "../includes/database.php";

$message = "";
$error = "";

// Debe llegar un token en la URL
if (!isset($_GET['token'])) {
    echo "<p>Invalid request.</p>";
    include "../includes/footer.php";
    exit;
}

$token = $_GET['token'];

$stmt = $conn->prepare("SELECT id FROM users WHERE reset_token=?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

// Si no existe ese token → link inválido
if ($result->num_rows != 1) {
    echo "<p>Invalid or expired reset link.</p>";
    include "../includes/footer.php";
    exit;
}

$user = $result->fetch_assoc();
$user_id = $user['id'];

// Cuando el usuario envía la nueva contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $new_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Guardamos la nueva contraseña y borramos el token
    $stmt = $conn->prepare("UPDATE users SET password_hash=?, reset_token=NULL WHERE id=?");
    $stmt->bind_param("si", $new_pass, $user_id);
    $stmt->execute();

    $message = "Your password has been updated! You can now log in.";
}
?>

<h2>Reset Your Password</h2>

<?php if ($message): ?>
    <p style="color:green;"><?= $message ?></p>
    <a href="login.php">Go to Login</a>
<?php else: ?>

<form method="post">
    New password:<br>
    <input type="password" name="password" required><br><br>
    <button type="submit">Change password</button>
</form>

<?php endif; ?>

<?php include "../includes/footer.php"; ?>
