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

/**
 * OJO: En tu BD orders tiene user_id NOT NULL.
 * Pero el MSE no te pasa el user_id de TTRPG (y no queremos crear usuarios espejo).
 * Solución simple: usar user_id = 1 como “system user” (o crea uno).
 */
$SYSTEM_USER_ID = 1;

$conn->begin_transaction();

try {
    // precio actual
    $stmt = $conn->prepare("SELECT price FROM items WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        $conn->rollback();
        http_response_code(404);
        echo json_encode(["success" => false, "error" => "Item not found"]);
        exit;
    }

    $price = (float)$row['price'];

    // crear order
    $o = $conn->prepare("INSERT INTO orders (user_id, status) VALUES (?, 'paid')");
    $o->bind_param("i", $SYSTEM_USER_ID);
    $o->execute();
    $order_id = (int)$conn->insert_id;

    // insertar order item
    $oi = $conn->prepare("INSERT INTO order_items (order_id, item_id, quantity, purchase_price) VALUES (?, ?, ?, ?)");
    $oi->bind_param("iiid", $order_id, $item_id, $quantity, $price);
    $oi->execute();

    $conn->commit();

    echo json_encode(["success" => true, "order_id" => $order_id]);

} catch (Throwable $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Server error"]);
}
