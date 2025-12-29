<?php
session_start();

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$base = preg_replace('#/public$#', '', $base);

if (empty($_SESSION['user']['id'])) {
  header("Location: {$base}/public/login.php");
  exit;
}

require __DIR__ . '/topbar.php';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Confirm logout</title>
</head>
<body style="padding:20px;">

<h2>Confirm logout</h2>
<p>Are you sure you want to log out?</p>

<button id="yes">Yes, logout</button>
<a href="<?= htmlspecialchars($base) ?>/public/index.php" style="margin-left:10px;">Cancel</a>

<pre id="dbg" style="margin-top:12px;background:#f5f5f5;padding:10px;border:1px solid #ddd;"></pre>

<script>
const BASE = <?= json_encode($base) ?>;

document.getElementById("yes").addEventListener("click", async () => {
  const dbg = document.getElementById("dbg");
  dbg.textContent = "Logging out...";

  try {
    const r = await fetch(BASE + "/api/auth/logout.php", { method: "POST" });
    const text = await r.text();
    dbg.textContent = "HTTP " + r.status + "\n" + text;
  } catch (e) {
    dbg.textContent = "Logout failed: " + e;
  }

  window.location.href = BASE + "/public/login.php";
});
</script>

</body>
</html>