<?php
header("Content-Type: application/json");

require "../includes/database.php";
require "auth.php"; // 🔐 API authentication

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

$order_ref = $data['order_id'] ?? null;
$items     = $data['items'] ?? [];

// Basic validation
if (!$order_ref || empty($items)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid input"]);
    exit;
}

// --------------------------------------------------
// 1. Create order in IA
// --------------------------------------------------
$stmt = $conn->prepare(
    "INSERT INTO orders (external_ref, status, created_at)
     VALUES (?, 'confirmed', NOW())"
);
$stmt->bind_param("s", $order_ref);
$stmt->execute();

$ia_order_id = $conn->insert_id;

// --------------------------------------------------
// 2. Insert order items
// --------------------------------------------------
$stmt_item = $conn->prepare(
    "INSERT INTO order_items (order_id, item_id, quantity, price)
     VALUES (?, ?, ?, ?)"
);

foreach ($items as $item) {

    $item_id  = $item['item_id'];
    $qty      = $item['quantity'];

    // Get current item price
    $stmt_price = $conn->prepare(
        "SELECT price FROM items WHERE id = ?"
    );
    $stmt_price->bind_param("i", $item_id);
    $stmt_price->execute();
    $price_res = $stmt_price->get_result();

    if ($price_res->num_rows === 0) {
        continue; // item not found, skip
    }

    $price = $price_res->fetch_assoc()['price'];

    $stmt_item->bind_param(
        "iiid",
        $ia_order_id,
        $item_id,
        $qty,
        $price
    );
    $stmt_item->execute();
}

// --------------------------------------------------
// 3. Respond OK
// --------------------------------------------------
echo json_encode([
    "success"     => true,
    "ia_order_id" => $ia_order_id
]);
