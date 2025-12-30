<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

require __DIR__ . "/../../includes/db.php";
$ias = require __DIR__ . "/../../includes/ia_config.php";

$userId = (int)($_SESSION['user']['id'] ?? 0);
if (!$userId) {
  http_response_code(401);
  echo json_encode(["success"=>false,"error"=>"Not logged in","items"=>[],"total"=>0]);
  exit;
}

$stmt = $conn->prepare("
  SELECT id, ia_name, ia_item_id, quantity
  FROM mse_carts
  WHERE user_id=?
  ORDER BY id DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$rs = $stmt->get_result();

$items = [];
$total = 0.0;

while ($row = $rs->fetch_assoc()) {
  $iaName = $row["ia_name"];
  if (!isset($ias[$iaName])) continue;
  $ia_conf = $ias[$iaName];

  // ✅ OJO: tu compañero usa item.php?id=...
  $url = $ia_conf["base_url"] . "item.php?id=" . (int)$row["ia_item_id"];

  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["X-API-KEY: " . $ia_conf["api_key"]],
    CURLOPT_TIMEOUT => 8
  ]);
  $resp = curl_exec($ch);
  $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($http !== 200 || !$resp) continue;

  $item = json_decode($resp, true);
  // Si tu IA devuelve { success:true, item:{...} } adaptamos:
  if (isset($item["item"])) $item = $item["item"];

  if (!isset($item["id"])) continue;

  $price = (float)($item["price"] ?? 0);
  $qty   = (int)$row["quantity"];
  $subtotal = $price * $qty;
  $total += $subtotal;

  $items[] = [
    "cart_id"  => (int)$row["id"],
    "ia"       => $iaName,
    "item_id"  => (int)$row["ia_item_id"],
    "name"     => (string)($item["name"] ?? ""),
    "price"    => $price,
    "quantity" => $qty,
    "subtotal" => $subtotal,
    "image"    => $item["image"] ?? null
  ];
}

$stmt->close();

echo json_encode([
  "success" => true,
  "items" => $items,
  "total" => $total
]);
