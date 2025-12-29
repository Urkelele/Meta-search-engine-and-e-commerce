<?php
header("Content-Type: application/json");

session_start();

require "../includes/database.php";
$ias = require "../includes/ia_config.php";

// --------------------------------------------------
// 1. Authentication check
// --------------------------------------------------
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Not authenticated"]);
    exit;
}

// --------------------------------------------------
// 2. Get cart items from MSE DB
// --------------------------------------------------
$stmt = $conn->prepare(
    "SELECT id, ia_name, ia_item_id, quantity
     FROM mse_carts
     WHERE user_id = ?"
);

$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();

$cart_items = $stmt->get_result();

// --------------------------------------------------
// 3. Build cart response
// --------------------------------------------------
$cart = [];
$total = 0;

while ($row = $cart_items->fetch_assoc()) {

    // IA config
    if (!isset($ias[$row['ia_name']])) {
        continue;
    }

    $ia_conf = $ias[$row['ia_name']];

    // --------------------------------------------------
    // Call IA item endpoint
    // --------------------------------------------------
    $url = $ia_conf['base_url'] . "item.php?id=" . $row['ia_item_id'];

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "X-API-KEY: " . $ia_conf['api_key']
        ],
        CURLOPT_TIMEOUT => 5
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        continue;
    }

    $item = json_decode($response, true);

    if (!isset($item['id'])) {
        continue;
    }

    // --------------------------------------------------
    // Compose cart item
    // --------------------------------------------------
    $subtotal = $item['price'] * $row['quantity'];
    $total += $subtotal;

    $cart[] = [
        "cart_id"  => $row['id'],
        "ia"       => $row['ia_name'],
        "item_id"  => $row['ia_item_id'],
        "name"     => $item['name'],
        "price"    => $item['price'],
        "quantity" => $row['quantity'],
        "subtotal" => $subtotal,
        "image"    => $item['image']
    ];
}

// --------------------------------------------------
// 4. Output
// --------------------------------------------------
echo json_encode([
    "items" => $cart,
    "total" => $total
]);
