<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$base = preg_replace('#/public$#', '', $base);
?>
<div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#f5f5f5; border-bottom:1px solid #ddd;">

  <div>
    <a href="<?= htmlspecialchars($base) ?>/public/index.php"
       style="text-decoration:none;font-weight:bold;">
      Home
    </a>
  </div>

  <div>
    <?php if (!empty($_SESSION['user']['email'])): ?>
      <span style="margin-right:12px;">
        <?= htmlspecialchars($_SESSION['user']['email']) ?>
      </span>

      <a href="<?= htmlspecialchars($base) ?>/public/cart.php"
         style="margin-right:12px; text-decoration:none;">
        Cart
      </a>

      <a href="<?= htmlspecialchars($base) ?>/public/logout_confirm.php"
         style="text-decoration:none;">
        Logout
      </a>

    <?php else: ?>
      <a href="<?= htmlspecialchars($base) ?>/public/login.php" style="margin-right:10px;">Login</a>
      <a href="<?= htmlspecialchars($base) ?>/public/register.php">Register</a>
    <?php endif; ?>
  </div>

</div>
