<?php
session_start();

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');   // /.../public
$base = preg_replace('#/public$#', '', $base);          // /... (raíz proyecto)

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
    <title>Your Orders</title>
</head>
<body>

<h1>Your Orders</h1>

<div id="orders"></div>

<script>
const BASE = <?= json_encode($base) ?>;

// ---------------------------------------------
// Load orders
// ---------------------------------------------
fetch(BASE + "/api/orders.php")
  .then(async r => {
    const text = await r.text();
    let j = null; try { j = JSON.parse(text); } catch(e) {}

    if (r.status === 401) {
      window.location.href = BASE + "/public/login.php";
      return;
    }

    if (!j) {
      document.getElementById("orders").innerHTML = "<pre>" + text + "</pre>";
      return;
    }

    return j;
  })
  .then(data => {
    if (!data) return;

    const div = document.getElementById("orders");
    div.innerHTML = "";

    if (!data.orders || !data.orders.length) {
      div.innerHTML = "<p>You have no orders yet.</p>";
      return;
    }

    data.orders.forEach(order => {
      let html = `
        <h3>
            Order #${order.order_id} – ${order.created_at}
            <span style="font-size:0.9em; opacity:0.8;">[${order.status}]</span>
        </h3>
        <table border="1" cellpadding="6">
          <tr>
            <th>Item</th>
            <th>IA</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Total</th>
          </tr>
      `;

      order.items.forEach(item => {
        html += `
          <tr>
            <td>${escapeHtml(item.name)}</td>
            <td>${escapeHtml(item.ia_name)}</td>
            <td>${item.price_at_purchase} €</td>
            <td>${item.quantity}</td>
            <td>${item.total} €</td>
          </tr>
        `;
      });

      html += `</table><br>`;
      div.innerHTML += html;
    });
  });

// ---------------------------------------------
// Escape HTML (avoid XSS)
// ---------------------------------------------
function escapeHtml(text) {
  return text
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");
}
</script>

</body>
</html>
