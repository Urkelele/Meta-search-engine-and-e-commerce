<?php
$mySqli = DB::get();
$message = "";
$toastClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';

    if (empty($email)) {
        $message = "Please enter your email address";
        $toastClass = "#dc3545";
    } else {
        // Check if email exists in users table
        $checkStmt = $mySqli->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $res = $checkStmt->get_result();

        if ($res->num_rows === 0) {
            $message = "Email not found in our system";
            $toastClass = "#dc3545";
        } else {
            $row = $res->fetch_assoc();
            $userId = $row['user_id'];

            // Generate password reset token
            $token = bin2hex(random_bytes(32));
            $expire = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiration

            // Insert token into tokens table
            $stmt = $mySqli->prepare("INSERT INTO tokens (user_id, token, type, expire_time) VALUES (?, ?, ?, ?)");
            $type = "password_reset";
            $stmt->bind_param("isss", $userId, $token, $type, $expire);

            if ($stmt->execute()) {
                // Send password reset email
                require_once __DIR__ . '/SendMail/sendMail.php';

                if (function_exists('SendPasswordRecover')) {
                    $sent = SendPasswordRecover($email, $token);
                    if ($sent) {
                        $message = "Password reset email sent. Check your inbox.";
                        $toastClass = "#28a745";
                    } else {
                        $message = "Password reset link generated, but email failed to send.";
                        $toastClass = "#ffc107";
                    }
                } else {
                    $message = "Password reset link generated, but email function not found.";
                    $toastClass = "#ffc107";
                }
            } else {
                $message = "Error generating reset link: " . $stmt->error;
                $toastClass = "#dc3545";
            }
            $stmt->close();
        }
        $checkStmt->close();
    }
    $mySqli->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href=
"https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href=
"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <link rel="shortcut icon" href=
"https://cdn-icons-png.flaticon.com/512/295/295128.png">
    <script src=
"https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <title>Registration</title>
</head>

<div class="container p-5 d-flex flex-column align-items-center">
    <?php if ($message): ?>
        <div class="toast align-items-center text-white border-0" 
          role="alert" aria-live="assertive" aria-atomic="true"
            style="background-color: <?php echo $toastClass; ?>;">
                <div class="d-flex">
                    <div class="toast-body">
                        <?php echo $message; ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
        </div>
    <?php endif; ?>
    <form method="post" class="form-control mt-5 p-4"
        style="height:auto; width:380px;
        box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px,
        rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;">
        <div class="row text-center">
            <i class="fa fa-user-circle-o fa-3x mt-1 mb-2" style="color: green;"></i>
            <h5 class="p-4" style="font-weight: 700;">Reset Your Password</h5>
        </div>
        <div class="mb-2">
            <label for="email"><i class="fa fa-envelope"></i> Email Address</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
        </div>
        <div class="mb-2 mt-3">
            <button type="submit" class="btn btn-success bg-success" style="font-weight: 600; width:100%;">Send Reset Link</button>
        </div>
        <div class="mb-2 mt-4">
            <p class="text-center" style="font-weight: 600; color: navy;">Remember your password? <a href="?page=login" style="text-decoration: none;">Login</a></p>
        </div>
        <div class="mb-2 mt-2">
            <p class="text-center" style="font-weight: 600; color: navy;">Don't have an account? <a href="?page=register" style="text-decoration: none;">Register</a></p>
        </div>
    </form>
</div>

<script>
    let toastElList = [].slice.call(document.querySelectorAll('.toast'))
    let toastList = toastElList.map(function (toastEl) {
        return new bootstrap.Toast(toastEl, { delay: 3000 });
    });
    toastList.forEach(toast => toast.show());
</script>
