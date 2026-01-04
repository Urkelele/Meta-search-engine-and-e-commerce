<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/auth.php";
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

// Release reserved stock
$db->begin_transaction();

try {
    // Check current reserved stock
    $stmt = $db->prepare("SELECT reserved_stock FROM products WHERE product_id = ? FOR UPDATE");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row || (int)$row['reserved_stock'] < $qty) {
        throw new Exception("Not enough reserved stock to release");
    }

    // Update stock values
    $upd = $db->prepare("
        UPDATE products
        SET available_stock = available_stock + ?,
            reserved_stock  = reserved_stock - ?
        WHERE product_id = ?
    ");
    $upd->bind_param("iii", $qty, $qty, $item_id);
    $upd->execute();

    $db->commit();
    echo json_encode(["success" => true]);

} catch (Exception $e) {
    $db->rollback();
    http_response_code(409);
    echo json_encode(["error" => $e->getMessage()]);
}
