<?php
$confirmed = isset($_POST['confirm_logout']);

if ($confirmed) {
    // User confirmed logout - destroy session
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }

    session_destroy();

    header('Location: index.php?page=home');
    exit();
}

// If not confirmed, show confirmation page
?>

<div class="container p-5 d-flex flex-column align-items-center">
    <div class="card mt-5" style="width: 400px; box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;">
        <div class="card-body text-center">
            <i class="fa fa-sign-out fa-3x mb-3" style="color: orange;"></i>
            <h4 class="card-title mb-3">Confirm Logout</h4>
            <p class="card-text mb-4">Are you sure you want to log out?</p>

            <form method="post" class="d-flex gap-2 justify-content-center">
                <button type="submit" name="confirm_logout" class="btn btn-danger" style="font-weight: 600;">Yes, Logout</button>
                <a href="index.php?page=home" class="btn btn-secondary" style="font-weight: 600; text-decoration: none;">Cancel</a>
            </form>
        </div>
    </div>
</div>