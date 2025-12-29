<?php
header("Content-Type: application/json");

session_start();

require "../includes/database.php";
$ias = require "../includes/ia_config.php";

// --------------------------------------------------
// 1. Check authentication
// --------------------------------------------------
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Not authenticated"]);
    exit;
}

// --------------------------------------------------
// 2. Read JSON input
// --------------------------------------------------
$data = json_decode(file_get_contents("php://input"), true);

$cart_id = $data['cart_id'] ?? null;

if (!$cart_id) {
    http_response_code(400);
    echo json_encode(["error" => "Missing cart_id"]);
    exit;
}

// --------------------------------------------------
// 3. Fetch cart item
// --------------------------------------------------
$stmt = $conn->prepare(
    "SELECT ia_name, ia_item_id, quantity
     FROM mse_carts
     WHERE id = ? AND user_id = ?"
);

$stmt->bind_param("ii", $cart_id, $_SESSION['user_id']);
$stmt->execute();

$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    http_response_code(404);
    echo json_encode(["error" => "Cart item not found"]);
    exit;
}

// --------------------------------------------------
// 4. Call IA release endpoint
// --------------------------------------------------
$ia_conf = $ias[$item['ia_name']];

$url = $ia_conf['base_url'] . "release.php";

$payload = json_encode([
    "item_id"  => $item['ia_item_id'],
    "quantity" => $item['quantity']
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

// --------------------------------------------------
// 5. Remove from MSE cart
// --------------------------------------------------
$stmt = $conn->prepare("DELETE FROM mse_carts WHERE id = ?");
$stmt->bind_param("i", $cart_id);
$stmt->execute();

// --------------------------------------------------
// 6. Respond OK
// --------------------------------------------------
echo json_encode([
    "success" => true
]);
