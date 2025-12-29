<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../../includes/db.php";
$ias = require __DIR__ . "/../../includes/ia_config.php";

// Auth
$userId = (int)($_SESSION['user']['id'] ?? 0);
if (!$userId) {
  http_response_code(401);
  echo json_encode(["success" => false, "error" => "Not logged in"]);
  exit;
}

// Get cart items
$stmt = $conn->prepare("
  SELECT id, ia_name, ia_item_id, quantity
  FROM mse_carts
  WHERE user_id = ?
  ORDER BY id DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
$total = 0.0;

while ($row = $res->fetch_assoc()) {
  $iaName  = $row['ia_name'];
  $itemId  = (int)$row['ia_item_id'];
  $qty     = (int)$row['quantity'];

  if (!isset($ias[$iaName])) continue;
  $ia_conf = $ias[$iaName];

  // Call IA item endpoint (compatible con item.php?id=...)
  $url = $ia_conf['base_url'] . "item.php?id=" . $itemId;

  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ["X-API-KEY: " . $ia_conf['api_key']],
    CURLOPT_TIMEOUT        => 5
  ]);
  $response = curl_exec($ch);
  $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($http !== 200 || !$response) continue;

  $data = json_decode($response, true);
  if (!is_array($data)) continue;

  // Soporta 2 formatos:
  // 1) { success:true, item:{...} }
  // 2) { id:..., name:..., price:... }
  $item = $data['item'] ?? $data;

  if (empty($item['name']) || !isset($item['price'])) continue;

  $price = (float)$item['price'];
  $subtotal = $price * $qty;
  $total += $subtotal;

  $items[] = [
    "cart_id"  => (int)$row['id'],
    "ia_name"  => $iaName,
    "item_id"  => $itemId,
    "name"     => (string)$item['name'],
    "price"    => $price,
    "quantity" => $qty,
    "subtotal" => round($subtotal, 2),
    "image"    => $item['image'] ?? null
  ];
}

echo json_encode([
  "success" => true,
  "items"   => $items,
  "total"   => round($total, 2)
]);
