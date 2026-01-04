<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

require __DIR__ . "/../../includes/db.php";
$ias = require __DIR__ . "/../../includes/ia_config.php";

$userId = (int)($_SESSION['user']['id'] ?? 0);
if (!$userId) {
  http_response_code(401);
  echo json_encode(["success"=>false,"error"=>"Not logged in"]);
  exit;
}

$data = json_decode(file_get_contents("php://input"), true) ?: [];
$ia       = $data["ia"] ?? "";
$itemId   = (int)($data["item_id"] ?? 0);
$quantity = max(1, (int)($data["quantity"] ?? 1));

if ($ia === "" || $itemId <= 0 || $quantity <= 0) {
  http_response_code(400);
  echo json_encode(["success"=>false,"error"=>"Invalid input"]);
  exit;
}
if (!isset($ias[$ia])) {
  http_response_code(400);
  echo json_encode(["success"=>false,"error"=>"Unknown IA"]);
  exit;
}

$ia_conf = $ias[$ia];

// Reserv stock en IA
$url = $ia_conf["base_url"] . "reserve.php";
$payload = json_encode(["item_id"=>$itemId, "quantity"=>$quantity]);

$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => $payload,
  CURLOPT_HTTPHEADER => [
    "Content-Type: application/json",
    "X-API-KEY: " . $ia_conf["api_key"]
  ],
  CURLOPT_TIMEOUT => 8
]);

$response = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http !== 200) {
  http_response_code(409);
  echo json_encode([
    "success"=>false,
    "error"=>"Stock could not be reserved",
    "ia_http"=>$http,
    "ia_response"=> json_decode($response, true)
  ]);
  exit;
}

$stmt = $conn->prepare("
  SELECT id, quantity
  FROM mse_carts
  WHERE user_id=? AND ia_name=? AND ia_item_id=?
  LIMIT 1
");
$stmt->bind_param("isi", $userId, $ia, $itemId);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Insert or update
if ($existing) {
  $newQty = (int)$existing["quantity"] + $quantity;
  $stmt = $conn->prepare("UPDATE mse_carts SET quantity=?, created_at=CURDATE() WHERE id=?");
  $stmt->bind_param("ii", $newQty, $existing["id"]);
  $stmt->execute();
  $stmt->close();
} else {
  $stmt = $conn->prepare("
    INSERT INTO mse_carts (user_id, ia_name, created_at, ia_item_id, quantity)
    VALUES (?, ?, CURDATE(), ?, ?)
  ");
  $stmt->bind_param("isii", $userId, $ia, $itemId, $quantity);
  $stmt->execute();
  $stmt->close();
}

echo json_encode(["success"=>true]);
