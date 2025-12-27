<?php
include "../includes/header.php";
include "../includes/auth.php";
require_admin(); // Solo admins pueden entrar
?>

<h2>Admin Dashboard</h2>

<ul>
    <li><a href="items.php">Manage Items</a></li>
    <li><a href="orders.php">Manage Orders</a></li>
    <li><a href="users.php">Manage Users</a></li>
</ul>

<?php include "../includes/footer.php"; ?>
