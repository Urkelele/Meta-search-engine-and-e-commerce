<?php
$base = $GLOBALS['BASE'] ?? '';
?>

<h2>Register</h2>

<form id="f">
  <input type="email" name="email" placeholder="Email" required><br><br>
  <input type="password" name="password" placeholder="Password" required><br><br>
  <button type="submit">Create account</button>

  <!-- login -->
  <a href="<?= htmlspecialchars($base) ?>/public/index.php?page=login" style="margin-left:10px;">
    Go to Login
  </a>
</form>

<div id="errorMsg" style="color:#b00020;margin-top:10px;display:none;"></div>

<script>
const BASE = <?= json_encode($base) ?>;

// handle register form submit
document.getElementById("f").addEventListener("submit", async (e) => {
  e.preventDefault();

  const body = Object.fromEntries(new FormData(e.target).entries());
  const errorBox = document.getElementById("errorMsg");
  errorBox.style.display = "none";

  try {
    const r = await fetch(BASE + "/api/auth/register.php", {
      method: "POST",
      headers: {"Content-Type":"application/json"},
      body: JSON.stringify(body),
      credentials: "same-origin"
    });

    const j = await r.json().catch(() => null);
    if (!j) return;

    if (!j.success) {
      errorBox.textContent = j.warning || "Registration failed";
      errorBox.style.display = "block";
      return;
    }

    window.location.href =
      BASE + "/public/index.php?page=login&email=" + encodeURIComponent(body.email);


  } catch (err) {
    errorBox.textContent = "Network error. Please try again.";
    errorBox.style.display = "block";
  }
});
</script>
