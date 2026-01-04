<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/auth.php";
require_api_key();

require_once __DIR__ . "/../DataBaseManagement/DB.php";
$db = DB::get();

$data = json_decode(file_get_contents("php://input"), true) ?: [];
$item_id = (int)($data["item_id"] ?? 0);
$qty     = max(1, (int)($data["quantity"] ?? 1));

if ($item_id <= 0) {
  http_response_code(400);
  echo json_encode(["success" => false, "error" => "Missing item_id"]);
  exit;
}

$db->begin_transaction();

try {
  // Reserve stock check
  $stmt = $db->prepare("
    SELECT product_id, price, reserved_stock
    FROM products
    WHERE product_id = ?
    FOR UPDATE
  ");
  $stmt->bind_param("i", $item_id);
  $stmt->execute();
  $p = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$p) {
    http_response_code(404);
    throw new Exception("Product not found");
  }

  if ((int)$p["reserved_stock"] < $qty) {
    http_response_code(409);
    throw new Exception("Not enough reserved stock (reserve first)");
  }

  $unitPrice = (float)$p["price"];
  $total = $unitPrice * $qty;

  // Check or create INTERNAL MSE USER
  $systemEmail = "mse@system.local";

  $u = $db->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
  $u->bind_param("s", $systemEmail);
  $u->execute();
  $ur = $u->get_result()->fetch_assoc();
  $u->close();

  if (!$ur) {
    // Create internal user
    $pass = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    $name = "MSE System";
    $isVerified = 1;
    $isAdmin = 0;

    $insU = $db->prepare("
      INSERT INTO users (email, password, name, is_verified, is_admin)
      VALUES (?, ?, ?, ?, ?)
    ");
    $insU->bind_param("sssii", $systemEmail, $pass, $name, $isVerified, $isAdmin);
    $insU->execute();
    $systemUserId = (int)$db->insert_id;
    $insU->close();
  } else {
    $systemUserId = (int)$ur["user_id"];
  }

  // Create order
  $status = "paid";

  $o = $db->prepare("
    INSERT INTO orders (user_id, total_price, status)
    VALUES (?, ?, ?)
  ");
  $o->bind_param("ids", $systemUserId, $total, $status);
  $o->execute();
  $orderId = (int)$db->insert_id;
  $o->close();

  // Insert order item
  $oi = $db->prepare("
    INSERT INTO order_items (order_id, product_id, quantity, price)
    VALUES (?, ?, ?, ?)
  ");
  $oi->bind_param("iiid", $orderId, $item_id, $qty, $unitPrice);
  $oi->execute();
  $oi->close();

  // Decrease reserved stock
  $up = $db->prepare("
    UPDATE products
    SET reserved_stock = reserved_stock - ?
    WHERE product_id = ?
  ");
  $up->bind_param("ii", $qty, $item_id);
  $up->execute();
  $up->close();

  $db->commit();

  echo json_encode([
    "success"  => true,
    "order_id" => $orderId
  ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
  $db->rollback();
  if (http_response_code() === 200) http_response_code(409);
  echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
