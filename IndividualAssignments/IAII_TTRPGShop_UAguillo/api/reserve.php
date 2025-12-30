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

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("SELECT stock FROM items WHERE id = ? FOR UPDATE");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        $conn->rollback();
        http_response_code(404);
        echo json_encode(["success" => false, "error" => "Item not found"]);
        exit;
    }

    if ((int)$row['stock'] < $quantity) {
        $conn->rollback();
        http_response_code(409);
        echo json_encode(["success" => false, "error" => "Not enough stock"]);
        exit;
    }

    $upd = $conn->prepare("UPDATE items SET stock = stock - ? WHERE id = ?");
    $upd->bind_param("ii", $quantity, $item_id);
    $upd->execute();

    $conn->commit();

    echo json_encode(["success" => true, "item_id" => $item_id, "reserved_quantity" => $quantity]);

} catch (Throwable $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Server error"]);
}
