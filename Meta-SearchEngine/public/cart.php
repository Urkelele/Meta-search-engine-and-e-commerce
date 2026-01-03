<?php
session_start();

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');   // /.../public
$base = preg_replace('#/public$#', '', $base);          // /... (project root)

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
    <title>Your Cart</title>
</head>
<body>

<h1>Your Cart</h1>

<div id="cart"></div>

<button onclick="checkout()">Checkout</button>

<script>
const BASE = <?= json_encode($base) ?>;

// Load cart items
fetch(BASE + "/api/cart/view.php")
  .then(async r => {
    const text = await r.text();
    let j = null; try { j = JSON.parse(text); } catch(e) {}
    if (r.status === 401) {
      window.location.href = BASE + "/public/login.php";
      return;
    }
    if (!j) {
      document.getElementById("cart").innerHTML = "<pre>" + text + "</pre>";
      return;
    }
    return j;
  })
  .then(data => {
    if (!data) return;

    const div = document.getElementById("cart");
    div.innerHTML = "";

    if (!data.items || !data.items.length) {
      div.innerHTML = "<p>Cart is empty</p>";
      return;
    }

    data.items.forEach(item => {
      div.innerHTML += `
        <p>
          ${item.name} x ${item.quantity}
          (${Number(item.subtotal).toFixed(2)} €)
          <button onclick="removeItem(${item.cart_id})">Remove</button>
        </p>
      `;
    });

    div.innerHTML += `<p><b>Total: ${Number(data.total).toFixed(2)} €</b></p>`;
});


function removeItem(cartId) {
  fetch(BASE + "/api/cart/remove.php", {
    method: "POST",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify({ cart_id: cartId })
  })
  .then(() => location.reload());
}

function checkout() {
  window.location.href = BASE + "/public/checkout.php";
}
</script>

</body>
</html>
