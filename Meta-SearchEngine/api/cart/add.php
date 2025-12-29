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

// Input
$data = json_decode(file_get_contents("php://input"), true);
$iaName   = $data['ia_name'] ?? ($data['ia'] ?? null);
$itemId   = isset($data['item_id']) ? (int)$data['item_id'] : 0;
$qty      = isset($data['quantity']) ? (int)$data['quantity'] : 0;

if (!$iaName || $itemId <= 0 || $qty <= 0) {
  http_response_code(400);
  echo json_encode(["success" => false, "error" => "Invalid input"]);
  exit;
}

if (!isset($ias[$iaName])) {
  http_response_code(400);
  echo json_encode(["success" => false, "error" => "Unknown IA"]);
  exit;
}

$ia_conf = $ias[$iaName];

// Call reserve endpoint
$url = $ia_conf['base_url'] . "reserve.php";
$payload = json_encode(["item_id" => $itemId, "quantity" => $qty]);

$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST           => true,
  CURLOPT_POSTFIELDS     => $payload,
  CURLOPT_HTTPHEADER     => [
    "Content-Type: application/json",
    "X-API-KEY: " . $ia_conf['api_key']
  ],
  CURLOPT_TIMEOUT => 5
]);

$response = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http !== 200) {
  http_response_code(409);
  echo json_encode([
    "success" => false,
    "error" => "Stock could not be reserved",
    "ia_response" => json_decode($response, true)
  ]);
  exit;
}

// Store in MSE cart (si existe, suma qty)
$stmt = $conn->prepare("
  SELECT id, quantity
  FROM mse_carts
  WHERE user_id = ? AND ia_name = ? AND ia_item_id = ?
  LIMIT 1
");
$stmt->bind_param("isi", $userId, $iaName, $itemId);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existing) {
  $newQty = (int)$existing['quantity'] + $qty;
  $upd = $conn->prepare("UPDATE mse_carts SET quantity = ?, created_at = NOW() WHERE id = ?");
  $upd->bind_param("ii", $newQty, $existing['id']);
  $upd->execute();
  $upd->close();
} else {
  $ins = $conn->prepare("
    INSERT INTO mse_carts (user_id, ia_name, ia_item_id, quantity, created_at)
    VALUES (?, ?, ?, ?, NOW())
  ");
  $ins->bind_param("isii", $userId, $iaName, $itemId, $qty);
  $ins->execute();
  $ins->close();
}

echo json_encode(["success" => true]);
