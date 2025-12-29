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

$item_id  = $data['item_id'] ?? null;
$quantity = $data['quantity'] ?? 0;

if (!$item_id || $quantity <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid input"]);
    exit;
}

// Create order
$stmt = $conn->prepare(
    "INSERT INTO orders (status, created_at)
     VALUES ('confirmed', NOW())"
);
$stmt->execute();

$ia_order_id = $conn->insert_id;

// Get price
$stmt = $conn->prepare("SELECT price FROM items WHERE id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Item not found"]);
    exit;
}

$price = $res->fetch_assoc()['price'];

// Insert order item
$stmt = $conn->prepare(
    "INSERT INTO order_items (order_id, item_id, quantity, price)
     VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("iiid", $ia_order_id, $item_id, $quantity, $price);
$stmt->execute();

// Respond
echo json_encode([
    "success"  => true,
    "order_id" => $ia_order_id
]);
