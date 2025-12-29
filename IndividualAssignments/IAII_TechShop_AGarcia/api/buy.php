<?php
require_once __DIR__ . "/_auth.php";
require_api_key();

require_once __DIR__ . "/../DataBaseManagement/DB.php";
$db = DB::get();

$data = json_decode(file_get_contents("php://input"), true);
$item_id = (int)($data['item_id'] ?? 0);
$qty = max(1, (int)($data['quantity'] ?? 1));

if ($item_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Missing item_id"]);
    exit;
}

$internalUserId = 1; // <-- pon aquí el user_id del usuario interno del MSE en tu IA

$db->begin_transaction();

try {
    // lock product
    $stmt = $db->prepare("SELECT price, shipping_price, reserved_stock FROM products WHERE product_id = ? FOR UPDATE");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();

    if (!$p || (int)$p['reserved_stock'] < $qty) {
        throw new Exception("Not enough reserved stock");
    }

    // consume reserved
    $upd = $db->prepare("
        UPDATE products
        SET reserved_stock = reserved_stock - ?
        WHERE product_id = ?
    ");
    $upd->bind_param("ii", $qty, $item_id);
    $upd->execute();

    // create local order
    $total = ($qty * (float)$p['price']) + ((float)$p['shipping_price']); // shipping una vez
    $insO = $db->prepare("INSERT INTO orders (user_id, total_price, status, created_at) VALUES (?, ?, 'paid', NOW())");
    $insO->bind_param("id", $internalUserId, $total);
    $insO->execute();
    $orderId = $db->insert_id;

    $insI = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $price = (float)$p['price'];
    $insI->bind_param("iiid", $orderId, $item_id, $qty, $price);
    $insI->execute();

    $db->commit();
    echo json_encode(["order_id" => (string)$orderId, "success" => true]);

} catch (Exception $e) {
    $db->rollback();
    http_response_code(409);
    echo json_encode(["error" => $e->getMessage()]);
}
