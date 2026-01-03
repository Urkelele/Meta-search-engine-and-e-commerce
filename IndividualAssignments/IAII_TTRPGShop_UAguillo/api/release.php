<?php
header("Content-Type: application/json; charset=utf-8");

require __DIR__ . "/auth.php";
require_api_key();

require __DIR__ . "/../includes/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true) ?: [];
$item_id  = (int)($data['item_id'] ?? 0);
$quantity = (int)($data['quantity'] ?? 0);

if ($item_id <= 0 || $quantity <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid input"]);
    exit;
}

// Release reserved stock
$stmt = $conn->prepare("UPDATE items SET stock = stock + ? WHERE id = ?");
$stmt->bind_param("ii", $quantity, $item_id);
$stmt->execute();

echo json_encode(["success" => true, "item_id" => $item_id, "released_quantity" => $quantity]);
