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

$item_id  = $data['item_id']  ?? null;
$quantity = $data['quantity'] ?? 1;

// Basic validation
if (!$item_id || $quantity <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid input"]);
    exit;
}

// --------------------------------------------------
// 1. Check item exists
// --------------------------------------------------
$stmt = $conn->prepare(
    "SELECT id FROM items WHERE id = ?"
);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Item not found"]);
    exit;
}

// --------------------------------------------------
// 2. Restore stock
// --------------------------------------------------
$stmt = $conn->prepare(
    "UPDATE items SET stock = stock + ? WHERE id = ?"
);
$stmt->bind_param("ii", $quantity, $item_id);
$stmt->execute();

// --------------------------------------------------
// 3. Respond OK
// --------------------------------------------------
echo json_encode([
    "success" => true,
    "item_id" => $item_id,
    "released_quantity" => $quantity
]);
