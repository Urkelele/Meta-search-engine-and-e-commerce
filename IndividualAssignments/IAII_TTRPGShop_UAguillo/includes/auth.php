<?php
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function require_admin() {
    if (!isset($_SESSION['user_admin']) || $_SESSION['user_admin'] != 1) {
        header("Location: ../public/login.php");
        exit;
    }
}
?>