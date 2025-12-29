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
$cartId = isset($data['cart_id']) ? (int)$data['cart_id'] : 0;

if ($cartId <= 0) {
  http_response_code(400);
  echo json_encode(["success" => false, "error" => "Missing cart_id"]);
  exit;
}

// Fetch cart row (owned by user)
$stmt = $conn->prepare("
  SELECT ia_name, ia_item_id, quantity
  FROM mse_carts
  WHERE id = ? AND user_id = ?
  LIMIT 1
");
$stmt->bind_param("ii", $cartId, $userId);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$item) {
  http_response_code(404);
  echo json_encode(["success" => false, "error" => "Cart item not found"]);
  exit;
}

$iaName = $item['ia_name'];
if (!isset($ias[$iaName])) {
  http_response_code(400);
  echo json_encode(["success" => false, "error" => "Unknown IA"]);
  exit;
}
$ia_conf = $ias[$iaName];

// Call release endpoint
$url = $ia_conf['base_url'] . "release.php";
$payload = json_encode([
  "item_id"  => (int)$item['ia_item_id'],
  "quantity" => (int)$item['quantity']
]);

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
curl_exec($ch);
curl_close($ch);

// Delete from cart
$del = $conn->prepare("DELETE FROM mse_carts WHERE id = ? AND user_id = ?");
$del->bind_param("ii", $cartId, $userId);
$del->execute();
$del->close();

echo json_encode(["success" => true]);
