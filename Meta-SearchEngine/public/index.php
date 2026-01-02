<?php
session_start();
require __DIR__ . '/topbar.php';

// Calcula BASE como hicimos antes (para poder redirigir/llamar rutas bien)
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');  // /.../public
$base = preg_replace('#/public$#', '', $base);         // /... (raíz proyecto)
?>
<!DOCTYPE html>
<html>
<head>
    <title>MSE Shop</title>
</head>
<body>

<h1>Meta Search Shop</h1>

<div id="toast" style="
  position:fixed;
  top:50%;
  left:50%;
  transform:translate(-50%,-50%);
  background:#222;
  color:#fff;
  padding:14px 18px;
  border-radius:10px;
  display:none;
  z-index:9999;
  box-shadow:0 8px 30px rgba(0,0,0,.35);
  font-size:16px;
">
</div>

<form id="searchForm">
    <input type="text" name="q" placeholder="Search products...">
    <button type="submit">Search</button>
</form>

<hr>

<div id="results"></div>

<script>
const BASE = <?= json_encode($base) ?>;

// ✅ Toast (Paso 3B)
function showToast(text) {
  const t = document.getElementById("toast");
  t.textContent = text;
  t.style.display = "block";
  setTimeout(() => t.style.display = "none", 2000);
}

// ---------------------------------------------
// Search handler
// ---------------------------------------------
document.getElementById("searchForm").onsubmit = async function(e) {
  e.preventDefault();

  const q = this.q.value;

  const r = await fetch(BASE + "/api/search.php?q=" + encodeURIComponent(q));
  const data = await r.json();

  const container = document.getElementById("results");
  container.innerHTML = "";

  if (!data.items || !data.items.length) {
    container.innerHTML = "<p>No results</p>";
    return;
  }

  data.items.forEach(item => {
  const div = document.createElement("div");
  div.style.border = "1px solid #ccc";
  div.style.margin = "10px";
  div.style.padding = "10px";

  div.innerHTML = `
    <h3>${item.name}</h3>
    <p>${item.price} €</p>
    <button onclick="seeProduct('${item.ia}', ${item.item_id})">
      See product
    </button>
    <button onclick="addToCart('${item.ia}', ${item.item_id}, 1)">
      Add to cart
    </button>
  `;

  container.appendChild(div);
});
}

function seeProduct(ia, itemId) {
  window.location.href = BASE + "/public/product.php?ia=" + encodeURIComponent(ia) + "&id=" + encodeURIComponent(itemId);
}

// ---------------------------------------------
// ✅ Add to cart con feedback + redirección opcional
// ---------------------------------------------
async function addToCart(ia, itemId, qty = 1) {
  const r = await fetch(BASE + "/api/cart/add.php", {
    method: "POST",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify({ ia: ia, item_id: itemId, quantity: qty })
  });

  let data = {};
  try { data = await r.json(); } catch(e) {}

  if (!r.ok || !data.success) {
    // Si no está loggeado, normalmente tu API devolverá 401
    if (r.status === 401) {
      showToast("You need to login first");
      window.location.href = BASE + "/public/login.php";
      return;
    }
    showToast("Error: " + (data.error || "cannot add"));
    return;
  }

  showToast("Added to cart ✅");

  // ✅ Actualiza contador del topbar si existe
  try {
    const vr = await fetch(BASE + "/api/cart/view.php");
    const vd = await vr.json();
    const el = document.getElementById("cartCount");
    if (el) el.textContent = (vd.items?.length || 0);
  } catch(e) {}

  // ✅ Si quieres que al añadir vaya al carrito, descomenta:
  // window.location.href = BASE + "/public/cart.php";
}
</script>