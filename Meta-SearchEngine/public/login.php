<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>

<h1>Login</h1>

<form id="loginForm">
    Email: <input type="email" name="email" required><br><br>
    Password: <input type="password" name="password" required><br><br>
    <button type="submit">Login</button>
</form>

<p id="msg"></p>

<script>
document.getElementById("loginForm").onsubmit = function(e) {
    e.preventDefault();

    fetch("../api/auth/login.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({
            email: this.email.value,
            password: this.password.value
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            window.location.href = "index.php";
        } else {
            document.getElementById("msg").innerText = res.error;
        }
    });
};
</script>

</body>
</html>
