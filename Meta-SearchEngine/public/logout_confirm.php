<?php
session_start();
require __DIR__ . "/topbar.php";

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$base = preg_replace('#/public$#', '', $base);
?>
<h2>Confirm logout</h2>
<p>Are you sure you want to logout?</p>

<form method="post" action="<?= $base ?>/api/auth/logout.php">
  <button type="submit">Yes, logout</button>
  <a href="<?= $base ?>/public/index.php" style="margin-left:10px;">Cancel</a>
</form>
