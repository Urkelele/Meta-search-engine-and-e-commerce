<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');  // /.../public
$base = preg_replace('#/public$#', '', $base);         // /... (raíz proyecto)

require __DIR__ . '/topbar.php';


?>
<h2>Login</h2>

<form id="f">
  <?php
  $prefillEmail = $_GET['email'] ?? '';
  ?>
  <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($prefillEmail) ?>"> <br><br>  

  <input type="password" name="password" placeholder="Password" required><br><br>

  <button type="submit">Login</button>

  <a href="<?= htmlspecialchars($base) ?>/public/register.php" style="margin-left:10px;">
    Go to Register
  </a>
</form>

<pre id="debug" style="margin-top:15px;background:#f5f5f5;padding:10px;border:1px solid #ddd;"></pre>

<script>
const BASE = <?= json_encode($base) ?>;

document.getElementById("f").addEventListener("submit", async (e) => {
  e.preventDefault();
  const body = Object.fromEntries(new FormData(e.target).entries());
  const dbg = document.getElementById("debug");

  const url = BASE + "/api/auth/login.php";
  dbg.textContent = "POST " + url + "\n\n" + JSON.stringify(body, null, 2);

  try {
    const r = await fetch(url, {
      method: "POST",
      headers: {"Content-Type":"application/json"},
      body: JSON.stringify(body)
    });

    const text = await r.text();
    dbg.textContent += "\n\nHTTP " + r.status + "\nRAW RESPONSE:\n" + text;

    let j = null;
    try { j = JSON.parse(text); } catch(e) {}
    if (!j) return;

    if (!j.success) {
      dbg.textContent += "\n\nError: " + (j.error || "Unknown");
      return;
    }

    // ✅ redirect al index cuando login OK
    window.location.href = BASE + "/public/index.php";

  } catch (err) {
    dbg.textContent += "\n\nFetch failed: " + err;
  }
});
</script>
