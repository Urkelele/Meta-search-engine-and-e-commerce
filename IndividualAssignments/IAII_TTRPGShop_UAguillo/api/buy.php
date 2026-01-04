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

// MSE internal user credentials
$MSE_EMAIL = "mse@internal";
$MSE_PASS  = password_hash("mse_internal_password", PASSWORD_BCRYPT);

$conn->begin_transaction();

try {
    // Obtain or create internal MSE USER
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $MSE_EMAIL);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if ($row) {
        $SYSTEM_USER_ID = (int)$row['id'];
    } else {
        $stmt = $conn->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
        $stmt->bind_param("ss", $MSE_EMAIL, $MSE_PASS);
        $stmt->execute();
        $SYSTEM_USER_ID = (int)$conn->insert_id;
        $stmt->close();
    }

    // check item price
    $stmt = $conn->prepare("SELECT price FROM items WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        throw new Exception("Item not found");
    }

    $price = (float)$row['price'];

    // create order
    $stmt = $conn->prepare("INSERT INTO orders (user_id, status) VALUES (?, 'paid')");
    $stmt->bind_param("i", $SYSTEM_USER_ID);
    $stmt->execute();
    $order_id = (int)$conn->insert_id;
    $stmt->close();

    // create order item
    $stmt = $conn->prepare("
        INSERT INTO order_items (order_id, item_id, quantity, purchase_price)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiid", $order_id, $item_id, $quantity, $price);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    echo json_encode(["success" => true, "order_id" => $order_id]);

} catch (Throwable $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Server error"]);
}
