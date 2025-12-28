<?php
header("Content-Type: application/json");

session_start();

require "../includes/database.php";
$ias = require "../includes/ia_config.php";

// --------------------------------------------------
// 1. Check user is logged in
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

$ia        = $data['ia']        ?? null;
$item_id  = $data['item_id']   ?? null;
$quantity = $data['quantity']  ?? 1;

if (!$ia || !$item_id || $quantity <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid input"]);
    exit;
}

// --------------------------------------------------
// 3. Check IA exists
// --------------------------------------------------
if (!isset($ias[$ia])) {
    http_response_code(400);
    echo json_encode(["error" => "Unknown IA"]);
    exit;
}

$ia_conf = $ias[$ia];

// --------------------------------------------------
// 4. Call IA reserve endpoint
// --------------------------------------------------
$url = $ia_conf['base_url'] . "reserve.php";

$payload = json_encode([
    "item_id"  => $item_id,
    "quantity" => $quantity
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

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// --------------------------------------------------
// 5. Handle IA response
// --------------------------------------------------
if ($http_code !== 200) {
    http_response_code(409);
    echo json_encode([
        "error" => "Stock could not be reserved",
        "ia_response" => json_decode($response, true)
    ]);
    exit;
}

// --------------------------------------------------
// 6. Store in MSE cart
// --------------------------------------------------
$stmt = $conn->prepare(
    "INSERT INTO mse_carts (user_id, ia_name, ia_item_id, quantity, created_at)
     VALUES (?, ?, ?, ?, NOW())"
);

$stmt->bind_param(
    "isii",
    $_SESSION['user_id'],
    $ia,
    $item_id,
    $quantity
);

$stmt->execute();

// --------------------------------------------------
// 7. Respond OK
// --------------------------------------------------
echo json_encode([
    "success" => true
]);
