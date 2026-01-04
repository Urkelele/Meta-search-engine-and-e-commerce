<?php
if (empty($_SESSION['user']['id'])) {
  header("Location: index.php?page=login");
  exit;
}

$base = $GLOBALS['BASE'] ?? '';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Checkout</title>
</head>
<body>
  <h1>Checkout</h1>

  <div id="cartBox" style="background:#f7f7f7;padding:10px;border:1px solid #ddd;margin-bottom:15px;"></div>

  <h3>Payment (mock)</h3>
  <form id="payForm">
    <input name="card_name" placeholder="Name on card" required><br><br>
    <input name="card_number" placeholder="Card number" required><br><br>
    <input name="exp" placeholder="MM/YY" required><br><br>
    <input name="cvv" placeholder="CVV" required><br><br>

    <button type="submit">Pay</button>
  </form>

  <pre id="msg" style="margin-top:10px;"></pre>

<script>
const BASE = <?= json_encode($base) ?>;

async function loadCart(){
  const r = await fetch(BASE + "/api/cart/view.php");
  const data = await r.json();

  const box = document.getElementById("cartBox");
  if (!data.items || data.items.length === 0) {
    box.innerHTML = "<p>Cart is empty</p>";
    return;
  }

  box.innerHTML = data.items.map(i =>
    `<div>${i.name} x ${i.quantity} — ${i.subtotal} €</div>`
  ).join("") + `<hr><b>Total: ${data.total} €</b>`;
}

loadCart();

document.getElementById("payForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  const body = Object.fromEntries(new FormData(e.target).entries());

  const r = await fetch(BASE + "/api/checkout.php", {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify(body)
  });

  const data = await r.json();
  const msg = document.getElementById("msg");

  if (!r.ok || !data.success) {
    msg.textContent = "ERROR: " + (data.error || "checkout failed");
    return;
  }

  // OK, return to index
  window.location.href = BASE + "/public/index.php?paid=1&order_id=" + data.order_id;
});
</script>
</body>
</html>
