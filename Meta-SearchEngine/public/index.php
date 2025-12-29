<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>MSE Shop</title>
</head>
<body>

<h1>Meta Search Shop</h1>

<form id="searchForm">
    <input type="text" name="q" placeholder="Search products...">
    <button type="submit">Search</button>
</form>

<hr>

<div id="results"></div>

<script>
// ---------------------------------------------
// Search handler
// ---------------------------------------------
document.getElementById("searchForm").onsubmit = function(e) {
    e.preventDefault();

    const q = this.q.value;

    fetch("../api/search.php?q=" + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {

            const container = document.getElementById("results");
            container.innerHTML = "";

            if (!data.items.length) {
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
                    <button onclick="addToCart('${item.ia}', ${item.item_id})">
                        Add to cart
                    </button>
                `;

                container.appendChild(div);
            });
        });
};

// ---------------------------------------------
// Add to cart
// ---------------------------------------------
function addToCart(ia, itemId) {
    fetch("../api/cart/add.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({
            ia: ia,
            item_id: itemId,
            quantity: 1
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alert("Added to cart");
        } else {
            alert("Error adding to cart");
        }
    });
}
</script>

<p>
    <a href="cart.php">View Cart</a>
</p>

</body>
</html>
