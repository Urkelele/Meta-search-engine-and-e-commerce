<?php
$base = $GLOBALS['BASE'] ?? '';
$prefillEmail = $_GET['email'] ?? '';

?>
<h2>Login</h2>

<form id="f">
  <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($prefillEmail) ?>"> <br><br>  

  <input type="password" name="password" placeholder="Password" required><br><br>

  <button type="submit">Login</button>

  <a href="<?= htmlspecialchars($base) ?>/public/index.php?page=register" style="margin-left:10px;">
    Go to Register
  </a>
</form>

<div id="errorMsg" style="color:#b00020;margin-top:10px;display:none;"></div>

<script>
const BASE = <?= json_encode($base) ?>;

document.getElementById("f").addEventListener("submit", async (e) => {
  e.preventDefault();

  const body = Object.fromEntries(new FormData(e.target).entries());
  const errorBox = document.getElementById("errorMsg");
  errorBox.style.display = "none";
  errorBox.textContent = "";

  try {
    const r = await fetch(BASE + "/api/auth/login.php", {
      method: "POST",
      headers: {"Content-Type":"application/json"},
      body: JSON.stringify(body),
      credentials: "same-origin"
    });

    const j = await r.json().catch(() => null);
    if (!j) {
      errorBox.textContent = "Unexpected server response.";
      errorBox.style.display = "block";
      return;
    }

    if (!j.success) {
      errorBox.textContent = j.error || "Login failed";
      errorBox.style.display = "block";
      return;
    }

    window.location.href = BASE + "/public/index.php?page=home";
  } catch (err) {
    errorBox.textContent = "Network error. Please try again.";
    errorBox.style.display = "block";
  }
});
</script>
