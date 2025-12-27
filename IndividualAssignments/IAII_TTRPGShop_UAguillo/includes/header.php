<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>TTRPG Shop</title>
</head>
<body>
<header>
    <h1>TTRPG Shop</h1>

    <nav>
        <a href="index.php">Home</a>

        <?php 
        // Show Cart and Search links only for non-admin users
        if (!isset($_SESSION['user_admin']) || $_SESSION['user_admin'] != 1): ?>
            
            <a href="search.php">Search</a>
            <a href="cart.php">Cart</a>

        <?php endif; ?>


        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="../public/logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>

</header>
<hr>

