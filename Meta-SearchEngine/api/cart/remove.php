<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

require __DIR__ . "/../../includes/db.php";
$ias = require __DIR__ . "/../../includes/ia_config.php";

$userId = (int)($_SESSION['user']['id'] ?? 0);
if (!$userId) { http_response_code(401); echo json_encode(["success"=>false,"error"=>"Not logged in"]); exit; }

$data = json_decode(file_get_contents("php://input"), true) ?: [];
$cartId = (int)($data["cart_id"] ?? 0);
if ($cartId <= 0) { http_response_code(400); echo json_encode(["success"=>false,"error"=>"Missing cart_id"]); exit; }

// Get cart item
$stmt = $conn->prepare("SELECT ia_name, ia_item_id, quantity FROM mse_carts WHERE id=? AND user_id=? LIMIT 1");
$stmt->bind_param("ii", $cartId, $userId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) { http_response_code(404); echo json_encode(["success"=>false,"error"=>"Cart item not found"]); exit; }

$ia = $row["ia_name"];
if (!isset($ias[$ia])) { http_response_code(400); echo json_encode(["success"=>false,"error"=>"Unknown IA"]); exit; }
$ia_conf = $ias[$ia];

// Release stock in IA
$url = $ia_conf["base_url"] . "release.php";
$payload = json_encode(["item_id" => (int)$row["ia_item_id"], "quantity" => (int)$row["quantity"]]);

$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => $payload,
  CURLOPT_HTTPHEADER => ["Content-Type: application/json", "X-API-KEY: ".$ia_conf["api_key"]],
  CURLOPT_TIMEOUT => 8
]);
$resp = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http !== 200) {
  http_response_code(409);
  echo json_encode(["success"=>false,"error"=>"IA release failed","ia_http"=>$http,"ia_response"=>json_decode($resp,true)]);
  exit;
}

// Erase from MSE cart
$stmt = $conn->prepare("DELETE FROM mse_carts WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $cartId, $userId);
$stmt->execute();
$stmt->close();

echo json_encode(["success"=>true]);
