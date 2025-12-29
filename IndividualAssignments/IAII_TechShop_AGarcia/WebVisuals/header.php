<!DOCTYPE html>
<html>
<head>
    <title>Tech Shop</title>
    <style>
        body { font-family: Arial; margin: 0; padding: 0; }
        .topbar {
            background: #222;
            color: #fff;
            padding: 10px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .topbar a {
            color: #fff;
            margin-left: 15px;
            text-decoration: none;
        }
        .menu {
            background: #eee;
            padding: 10px 15px;
        }
        .menu a {
            margin-right: 15px;
            text-decoration: none;
            color: #333;
        }
    </style>
</head>
<body>

<div class="topbar">
    <div>
        <strong>Tech Shop</strong>
    </div>

    <div>
        <!-- Checks if the user is logged -->
        <?php if (!empty($_SESSION['user'])): ?>
            <?php $user = $_SESSION['user']; ?>
            Welcome, <strong><?= htmlspecialchars($user['name']); if (!empty($user['is_admin'])): ?> (admin) <?php endif; ?></strong>
            |

            <?php
            $cartCount = 0;
            try {
                $db = DB::get();
                $cstmt = $db->prepare("SELECT SUM(quantity) as cnt FROM cart_items WHERE user_id = ?");
                if ($cstmt) {
                    $cstmt->bind_param('i', $user['id']);
                    $cstmt->execute();
                    $cres = $cstmt->get_result();
                    if ($cres) {
                        $crow = $cres->fetch_assoc();
                        $cartCount = (int)($crow['cnt'] ?? 0);
                    }
                    $cstmt->close();
                }
            } catch (Throwable $e) {
                $cartCount = 0;
            }
            ?>
            <!-- Checks if the user is admin -->
            <?php if (!empty($user['is_admin'])): ?>
                <a href="?page=admin">Admin</a>
            <?php else: ?>
                <a href="?page=cart">Cart<?php if ($cartCount > 0) echo ' (' . $cartCount . ')'; ?></a>
            <?php endif; ?>

            | <a href="?page=logout">Logout</a>
        <?php else: ?>
            <a href="?page=login">Login</a>
            <a href="?page=register">Register</a>
        <?php endif; ?>
    </div>
</div>

<!-- Prints all the buttons for the admin or the normal user and the not logged user -->
<div class="menu">
    <?php if (!empty($user['is_admin'])): ?>
        <a href="?page=admin">Home</a>
        <a href="?page=admin_products">Manage Products</a>
        <a href="?page=admin_orders">Manage Orders</a>
        <a href="?page=admin_users">Manage Users</a>
    <?php else: ?>
        <a href="?page=home">Home</a>
        <a href="?page=products&category=1">Keyboards</a>
        <a href="?page=products&category=2">Mouses</a>
        <a href="?page=products&category=3">Monitors</a>
        <a href="?page=products&category=4">Headsets</a>
    <?php endif; ?>
</div>