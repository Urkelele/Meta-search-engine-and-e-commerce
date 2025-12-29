<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
</head>
<body>

<h1>Checkout</h1>

<p>Payment details (mock)</p>

<button onclick="pay()">Pay now</button>

<script>
function pay() {
    fetch("../api/checkout.php", {
        method: "POST"
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alert("Order completed! Order ID: " + res.order_id);
            window.location.href = "index.php";
        } else {
            alert("Payment failed");
        }
    });
}
</script>

</body>
</html>
