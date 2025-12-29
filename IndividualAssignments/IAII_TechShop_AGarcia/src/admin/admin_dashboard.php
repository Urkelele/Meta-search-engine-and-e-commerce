<?php
// Admin protection
if (empty($_SESSION['user']) || $_SESSION['user']['is_admin'] != 1) {
    http_response_code(403);
    exit('Admin only');
}
?>

<h1>Admin panel</h1>

<ul>
    <li><a href="?page=admin_products">Manage products</a></li>
    <li><a href="?page=admin_orders">Manage orders</a></li>
    <li><a href="?page=admin_users">Manage users</a></li>
</ul>