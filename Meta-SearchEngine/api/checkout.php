<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

require __DIR__ . "/../includes/db.php";          // $conn (mysqli)
$ias = require __DIR__ . "/../includes/ia_config.php";

$userId = (int)($_SESSION['user']['id'] ?? 0);
if (!$userId) {
  http_response_code(401);
  echo json_encode(["success"=>false,"error"=>"Not logged in"]);
  exit;
}

// pago mock (solo validar que han rellenado)
$data = json_decode(file_get_contents("php://input"), true) ?: [];
$card = trim($data['card_number'] ?? '');
$name = trim($data['card_name'] ?? '');
$exp  = trim($data['exp'] ?? '');
$cvv  = trim($data['cvv'] ?? '');

if ($card === '' || $name === '' || $exp === '' || $cvv === '') {
  http_response_code(400);
  echo json_encode(["success"=>false,"error"=>"Payment information incomplete"]);
  exit;
}

// 1) cargar carrito
$stmt = $conn->prepare("
  SELECT id, ia_name, ia_item_id, quantity
  FROM mse_carts
  WHERE user_id = ?
  ORDER BY id ASC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$cartRes = $stmt->get_result();

$cartItems = [];
while ($r = $cartRes->fetch_assoc()) $cartItems[] = $r;
$stmt->close();

if (!$cartItems) {
  http_response_code(409);
  echo json_encode(["success"=>false,"error"=>"Cart is empty"]);
  exit;
}

$conn->begin_transaction();

try {
  // 2) crear order en MSE (id autoincrement)
  $status = "paid";
  $stmt = $conn->prepare("INSERT INTO mse_orders (user_id, status, created_at) VALUES (?, ?, NOW())");
  $stmt->bind_param("is", $userId, $status);
  $stmt->execute();
  $orderId = (int)$conn->insert_id;
  $stmt->close();

  // 3) procesar items
  foreach ($cartItems as $item) {
    $iaName   = $item['ia_name'];
    $iaItemId = (int)$item['ia_item_id'];
    $qty      = (int)$item['quantity'];

    if (!isset($ias[$iaName])) throw new Exception("Unknown IA: $iaName");
    $ia_conf = $ias[$iaName];

    // (A) precio del item
    $itemUrl = $ia_conf['base_url'] . "item.php?id=" . $iaItemId;
    $ch = curl_init($itemUrl);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => ["X-API-KEY: " . $ia_conf['api_key']],
      CURLOPT_TIMEOUT => 10
    ]);
    $itemResp = curl_exec($ch);
    $itemCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($itemCode !== 200 || !$itemResp) {
      throw new Exception("Failed to fetch item info from IA ($iaName)");
    }

    $itemData = json_decode($itemResp, true);
    $itemObj  = $itemData['item'] ?? $itemData; // por si viene anidado
    $price    = (float)($itemObj['price'] ?? 0);

    // (B) buy en IA
    $buyUrl = $ia_conf['base_url'] . "buy.php";
    $payload = json_encode(["item_id" => $iaItemId, "quantity" => $qty]);

    $ch = curl_init($buyUrl);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $payload,
      CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "X-API-KEY: " . $ia_conf['api_key']
      ],
      CURLOPT_TIMEOUT => 15
    ]);
    $buyResp = curl_exec($ch);
    $buyCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($buyCode !== 200) {
      $err = json_decode($buyResp, true);
      $msg = $err['error'] ?? $buyResp ?? 'Unknown buy error';
      throw new Exception("Purchase failed for IA item ($iaName): " . $msg);
    }

    $buyData = json_decode($buyResp, true);
    $iaOrderRef = (string)($buyData['order_id'] ?? "N/A");

    // (C) guardar mse_order_items
    $stmt = $conn->prepare("
      INSERT INTO mse_order_items (order_id, ia_name, ia_item_id, quantity, price_at_purchase, ia_order_ref)
      VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("isiids", $orderId, $iaName, $iaItemId, $qty, $price, $iaOrderRef);
    $stmt->execute();
    $stmt->close();
  }

  // 4) vaciar carrito
  $stmt = $conn->prepare("DELETE FROM mse_carts WHERE user_id = ?");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $stmt->close();

  $conn->commit();
  echo json_encode(["success"=>true, "order_id"=>$orderId]);

} catch (Exception $e) {
  $conn->rollback();
  http_response_code(409);
  echo json_encode(["success"=>false, "error"=>$e->getMessage()]);
}
