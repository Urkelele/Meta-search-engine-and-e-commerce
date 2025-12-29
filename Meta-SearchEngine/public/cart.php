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
    <title>Your Cart</title>
</head>
<body>

<h1>Your Cart</h1>

<div id="cart"></div>

<button onclick="checkout()">Checkout</button>

<script>
// ---------------------------------------------
// Load cart
// ---------------------------------------------
fetch("../api/cart/view.php")
    .then(r => r.json())
    .then(data => {

        const div = document.getElementById("cart");
        div.innerHTML = "";

        if (!data.items.length) {
            div.innerHTML = "<p>Cart is empty</p>";
            return;
        }

        data.items.forEach(item => {
            div.innerHTML += `
                <p>
                    ${item.name} x ${item.quantity}
                    (${item.subtotal} €)
                    <button onclick="removeItem(${item.cart_id})">Remove</button>
                </p>
            `;
        });

        div.innerHTML += `<p><b>Total: ${data.total} €</b></p>`;
    });

// ---------------------------------------------
// Remove item
// ---------------------------------------------
function removeItem(cartId) {
    fetch("../api/cart/remove.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({ cart_id: cartId })
    })
    .then(() => location.reload());
}

// ---------------------------------------------
// Checkout
// ---------------------------------------------
function checkout() {
    window.location.href = "checkout.php";
}
</script>

</body>
</html>
