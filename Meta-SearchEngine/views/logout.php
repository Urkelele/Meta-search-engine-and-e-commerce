<?php
$base = $GLOBALS['BASE'] ?? '';

if (empty($_SESSION['user']['id'])) {
    header("Location: {$base}/public/index.php?page=home");
    exit;
}
?>

<h2>Confirm logout</h2>
<p>Are you sure you want to logout?</p>

<form method="post" action="<?= $base ?>/api/auth/logout.php">
  <button type="submit">Yes, logout</button>
  <a href="<?= $base ?>/public/index.php" style="margin-left:10px;">Cancel</a>
</form>
