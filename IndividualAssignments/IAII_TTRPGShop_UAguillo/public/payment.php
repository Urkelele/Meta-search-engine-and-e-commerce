<?php
include "../includes/header.php";
include "../includes/auth.php";
require_login(); // Ensure user is logged in
include "../includes/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<p>Your cart is empty. Nothing to pay.</p>";
    include "../includes/footer.php";
    exit;
}

// Payment processing simulation
echo "<h2>Processing payment...</h2>";
sleep(3);

echo "<p style='color:green;'>Payment successful!</p><hr>";

// Create order in DB
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO orders (user_id, status) VALUES (?, 'paid')");
$stmt->bind_param("i", $user_id);
$stmt->execute();

$order_id = $conn->insert_id;

// Order items insertion
foreach ($_SESSION['cart'] as $item_id => $data) {

    // Get item price
    $stmt = $conn->prepare("SELECT price FROM items WHERE id=?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    $price = $product['price'];
    $qty = $data['quantity'];

    // Insert in order_items
    $stmt = $conn->prepare("
        INSERT INTO order_items (order_id, item_id, quantity, purchase_price)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiid", $order_id, $item_id, $qty, $price);
    $stmt->execute();
}

// Clear cart
$_SESSION['cart'] = [];

// Show order confirmation
echo "<h2>Order Confirmation</h2>";
echo "<p>Your order number is <b>#$order_id</b></p>";

echo "<h3>Items:</h3>";

$stmt = $conn->prepare("
    SELECT order_items.*, items.name
    FROM order_items
    JOIN items ON order_items.item_id = items.id
    WHERE order_id=?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

$total_price = 0;

echo "<ul>";
while ($item = $result->fetch_assoc()) {
    $subtotal = $item['purchase_price'] * $item['quantity'];
    $total_price += $subtotal;

    echo "<li>" . htmlspecialchars($item['name']) .
         " - " . $item['quantity'] . " × " . $item['purchase_price'] .
         "€ = <b>$subtotal €</b></li>";
}
echo "</ul>";

echo "<h3>Total Paid: $total_price €</h3>";

echo "<br><a href='index.php'>Return to Shop</a>";

include "../includes/footer.php";
?>
