<?php
$mySqli = DB::get();
$message = "";
$toastClass = "";
$tokenValid = false;

if (empty($_GET['token'])) {
    $message = "Invalid reset link.";
    $toastClass = "#dc3545";
} else {
    $token = $_GET['token'];

    // Look for valid password reset token
    $stmt = $mySqli->prepare("SELECT user_id, expire_time FROM tokens WHERE token = ? AND type = 'password_reset' LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        $message = "Invalid or expired reset link.";
        $toastClass = "#dc3545";
    } else {
        $row = $res->fetch_assoc();
        $userId = $row['user_id'];
        $expire = $row['expire_time'];

        if (strtotime($expire) < time()) {
            $message = "Reset link has expired. Please request a new one.";
            $toastClass = "#ffc107";
        } else {
            $tokenValid = true;
        }
    }
    $stmt->close();
}

// Handle password reset form submission
if ($tokenValid && $_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($newPassword) || empty($confirmPassword)) {
        $message = "Please fill in all fields.";
        $toastClass = "#dc3545";
    } elseif ($newPassword !== $confirmPassword) {
        $message = "Passwords do not match.";
        $toastClass = "#dc3545";
    } else {
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update user password
        $updateStmt = $mySqli->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $updateStmt->bind_param("si", $hashedPassword, $userId);

        if ($updateStmt->execute()) {
            // Delete the used token
            $delStmt = $mySqli->prepare("DELETE FROM tokens WHERE token = ?");
            $delStmt->bind_param("s", $token);
            $delStmt->execute();
            $delStmt->close();

            $message = "Password reset successfully! You can now log in.";
            $toastClass = "#28a745";
            $tokenValid = false;
        } else {
            $message = "Error updating password: " . $updateStmt->error;
            $toastClass = "#dc3545";
        }
        $updateStmt->close();
    }
}

$mySqli->close();
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
                <button type="button" class="btn-close
                btn-close-white me-2 m-auto" 
                      data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($tokenValid): ?>
    <form method="post" class="form-control mt-5 p-4"
        style="height:auto; width:380px;
        box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px,
        rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;">
        <div class="row text-center">
            <i class="fa fa-key fa-3x mt-1 mb-2" style="color: green;"></i>
            <h5 class="p-4" style="font-weight: 700;">Set Your New Password</h5>
        </div>
        <div class="mb-3">
            <label for="new_password"><i class="fa fa-lock"></i> New Password</label>
            <div class="input-group">
                <input type="password" name="new_password" id="new_password"
                  class="form-control" required>
                <span class="input-group-text" id="togglePassword1" style="cursor: pointer;">
                    <i class="fa fa-eye"></i>
                </span>
            </div>
        </div>
        <div class="mb-3 mt-3">
            <label for="confirm_password"><i class="fa fa-lock"></i> Confirm Password</label>
            <div class="input-group">
                <input type="password" name="confirm_password" id="confirm_password"
                  class="form-control" required>
                <span class="input-group-text" id="togglePassword2" style="cursor: pointer;">
                    <i class="fa fa-eye"></i>
                </span>
            </div>
        </div>
        <div class="mb-3 mt-4">
            <button type="submit" 
              class="btn btn-success"
              style="font-weight: 600; width: 100%;">Reset Password</button>
        </div>
        <div class="mb-2 mt-3">
            <p class="text-center" style="font-weight: 600; 
            color: navy;">Remember your password? <a href="?page=login"
                    style="text-decoration: none;">Login</a></p>
        </div>
    </form>
    <?php else: ?>
    <div class="card mt-5" style="width: 380px;">
        <div class="card-body text-center">
            <h5 class="card-title">Password Reset</h5>
            <p class="card-text"><?php echo $message; ?></p>
            <a href="?page=forgot_password" class="btn btn-primary">Request New Reset Link</a>
            <a href="?page=login" class="btn btn-secondary mt-2">Go to Login</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
    let toastElList = [].slice.call(document.querySelectorAll('.toast'))
    let toastList = toastElList.map(function (toastEl) {
        return new bootstrap.Toast(toastEl, { delay: 3000 });
    });
    toastList.forEach(toast => toast.show());

    // Toggle password visibility for new_password
    const togglePassword1 = document.querySelector("#togglePassword1");
    const newPassword = document.querySelector("#new_password");
    if (togglePassword1 && newPassword) {
        togglePassword1.addEventListener("click", function () {
            const type = newPassword.getAttribute("type") === "password" ? "text" : "password";
            newPassword.setAttribute("type", type);
            this.querySelector("i").classList.toggle("fa-eye");
            this.querySelector("i").classList.toggle("fa-eye-slash");
        });
    }

    // Toggle password visibility for confirm_password
    const togglePassword2 = document.querySelector("#togglePassword2");
    const confirmPassword = document.querySelector("#confirm_password");
    if (togglePassword2 && confirmPassword) {
        togglePassword2.addEventListener("click", function () {
            const type = confirmPassword.getAttribute("type") === "password" ? "text" : "password";
            confirmPassword.setAttribute("type", type);
            this.querySelector("i").classList.toggle("fa-eye");
            this.querySelector("i").classList.toggle("fa-eye-slash");
        });
    }
</script>
