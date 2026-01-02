<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$base = preg_replace('#/public$#', '', $base);

$isLogged = !empty($_SESSION['user']['id']);
$email = $_SESSION['user']['email'] ?? '';
?>
<div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:#333;color:#fff;">
  <div style="display:flex;gap:12px;align-items:center;">
    <a href="<?= $base ?>/public/index.php" style="color:#fff;text-decoration:none;font-weight:bold;">Home</a>
  </div>

  <div style="display:flex;gap:12px;align-items:center;">
    <?php if ($isLogged): ?>
      <span style="opacity:.85;"><?= htmlspecialchars($email) ?></span>

      <a href="<?= $base ?>/public/cart.php" style="color:#fff;text-decoration:none;">
        Cart (<span id="cartCount">0</span>)
      </a>

      <a href="<?= $base ?>/public/orders.php" style="color:#fff;text-decoration:none;">
        Orders
      </a>

      <a href="<?= $base ?>/public/logout_confirm.php" style="color:#fff;text-decoration:none;">Logout</a>
    <?php else: ?>
      <a href="<?= $base ?>/public/login.php" style="color:#fff;text-decoration:none;">Login</a>
      <a href="<?= $base ?>/public/register.php" style="color:#fff;text-decoration:none;">Register</a>
    <?php endif; ?>
  </div>
</div>

<?php if ($isLogged): ?>
<script>
(async () => {
  const BASE = <?= json_encode($base) ?>;
  try {
    const r = await fetch(BASE + "/api/cart/view.php");
    const data = await r.json();
    const totalQty = (data.items || []).reduce((acc, it) => acc + (parseInt(it.quantity || 1, 10)), 0);
    document.getElementById("cartCount").textContent = totalQty;
  } catch(e) {}
})();
</script>
<?php endif; ?>
