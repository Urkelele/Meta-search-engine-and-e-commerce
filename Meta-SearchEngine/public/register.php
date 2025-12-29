<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>

<h1>Register</h1>

<form id="registerForm">
    Email: <input type="email" name="email" required><br><br>
    Password: <input type="password" name="password" required><br><br>
    <button type="submit">Register</button>
</form>

<p id="msg"></p>

<script>
document.getElementById("registerForm").onsubmit = function(e) {
    e.preventDefault();

    fetch("../api/auth/register.php", {
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
            window.location.href = "login.php";
        } else {
            document.getElementById("msg").innerText = res.error;
        }
    });
};
</script>

</body>
</html>
