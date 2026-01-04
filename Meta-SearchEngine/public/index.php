<?php
session_start();
require __DIR__ . '/topbar.php';

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');  // /.../public
$base = preg_replace('#/public$#', '', $base);         // /... (project root)
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

  <select id="category" name="category">
    <option value="">All Categories</option>
  </select>

  <select id="subcategory" name="subcategory">
    <option value="">All Subcategories</option>
  </select>

  <button type="submit">Search</button>
</form>

<hr>

<div id="results"></div>

<script>
async function refreshCartCount() {
  try {
    const r = await fetch(BASE + "/api/cart/view.php", { cache: "no-store" });
    const data = await r.json();

    const el = document.getElementById("cartCount");
    if (!el) return;

    const totalQty = (data.items || []).reduce((acc, it) => acc + (parseInt(it.quantity || 1, 10)), 0);
    el.textContent = totalQty;
  } catch (e) {
    // fail silently
  }
}

const BASE = <?= json_encode($base) ?>;

function showToast(text) {
  const t = document.getElementById("toast");
  t.textContent = text;
  t.style.display = "block";
  setTimeout(() => t.style.display = "none", 2000);
}

function seeProduct(ia, itemId) {
  window.location.href = BASE + "/public/product.php?ia=" + encodeURIComponent(ia) + "&id=" + encodeURIComponent(itemId);
}

function renderItems(items) {
  const container = document.getElementById("results");
  container.innerHTML = "";

  if (!items || !items.length) {
    container.innerHTML = "<p>No results</p>";
    return;
  }

  items.forEach(item => {
    const div = document.createElement("div");
    div.style.border = "1px solid #ccc";
    div.style.margin = "10px";
    div.style.padding = "10px";

    div.innerHTML = `
      <h3>${item.name}</h3>
      <p>${item.price} €</p>
      <p style="color:#666;">${item.category || ""}</p>
      <button onclick="seeProduct('${item.ia}', ${item.item_id})">See product</button>
      <button onclick="addToCart('${item.ia}', ${item.item_id}, 1)">Add to cart</button>
    `;
    container.appendChild(div);
  });
}

function fillCategories(meta, selectedCat) {
  const sel = document.getElementById("category");
  const keep = sel.value;
  sel.innerHTML = `<option value="">All Categories</option>` +
    (meta?.categories || []).map(c =>
      `<option value="${encodeURIComponent(c)}">${c}</option>`
    ).join("");

  // restore previous selection if any
  if (selectedCat) sel.value = encodeURIComponent(selectedCat);
  else if (keep) sel.value = keep;
}

function fillSubcategories(meta, cat, selectedSub) {
  const sel = document.getElementById("subcategory");
  const list = (meta?.subcategories_by_category && cat)
    ? (meta.subcategories_by_category[cat] || [])
    : [];

  sel.innerHTML = `<option value="">All Subcategories</option>` +
    list.map(s => `<option value="${encodeURIComponent(s)}">${s}</option>`).join("");

  if (selectedSub) sel.value = encodeURIComponent(selectedSub);
}

async function loadProducts() {
  const q = document.querySelector('input[name="q"]').value.trim();
  const catEnc = document.getElementById("category").value;
  const subEnc = document.getElementById("subcategory").value;

  const category = catEnc ? decodeURIComponent(catEnc) : "";
  const subcategory = subEnc ? decodeURIComponent(subEnc) : "";

  const url = BASE + "/api/search.php?q=" + encodeURIComponent(q)
            + "&category=" + encodeURIComponent(category)
            + "&subcategory=" + encodeURIComponent(subcategory);

  const r = await fetch(url);
  const data = await r.json().catch(() => ({}));

  if (!data.success) {
    showToast("Search error");
    return;
  }

  fillCategories(data.meta, category);

  // subcategories dependent on selected category
  const currentCat = (document.getElementById("category").value)
    ? decodeURIComponent(document.getElementById("category").value)
    : "";

  fillSubcategories(data.meta, currentCat, subcategory);

  renderItems(data.items || []);
}

// submit search form
document.getElementById("searchForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  await loadProducts();
});

// auto-filter changing category
document.getElementById("category").addEventListener("change", async () => {
  // reset subcat
  document.getElementById("subcategory").value = "";
  await loadProducts();
});

// auto-filter changing subcategory
document.getElementById("subcategory").addEventListener("change", async () => {
  await loadProducts();
});

// on load
document.addEventListener("DOMContentLoaded", () => {
  loadProducts();
  refreshCartCount();
});

async function addToCart(ia, itemId, qty = 1) {
  const r = await fetch(BASE + "/api/cart/add.php", {
    method: "POST",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify({ ia: ia, item_id: itemId, quantity: qty })
  });

  let data = {};
  try { data = await r.json(); } catch(e) {}

  if (!r.ok || !data.success) {
    if (r.status === 401) {
      showToast("You need to login first");
      window.location.href = BASE + "/public/login.php";
      return;
    }
    showToast("Error: " + (data.error || "cannot add"));
    return;
  }

  showToast("Added to cart");

  // topbar refresh
  await refreshCartCount();
}
</script>