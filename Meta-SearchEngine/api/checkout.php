<?php
header("Content-Type: application/json");

session_start();

require "../includes/database.php";
$ias = require "../includes/ia_config.php";

// --------------------------------------------------
// 1. Authentication
// --------------------------------------------------
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Not authenticated"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// --------------------------------------------------
// 2. Get cart items
// --------------------------------------------------
$stmt = $conn->prepare(
    "SELECT id, ia_name, ia_item_id, quantity
     FROM mse_carts
     WHERE user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$cart_items = $stmt->get_result();

if ($cart_items->num_rows === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Cart is empty"]);
    exit;
}

// --------------------------------------------------
// 3. Create MSE order
// --------------------------------------------------
$conn->begin_transaction();

$stmt = $conn->prepare(
    "INSERT INTO mse_orders (user_id, status, created_at)
     VALUES (?, 'paid', NOW())"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$order_id = $conn->insert_id;

// --------------------------------------------------
// 4. Process each cart item
// --------------------------------------------------
while ($item = $cart_items->fetch_assoc()) {

    if (!isset($ias[$item['ia_name']])) {
        throw new Exception("Unknown IA");
    }

    $ia_conf = $ias[$item['ia_name']];

    // ----------------------------------------------
    // Call IA buy endpoint
    // ----------------------------------------------
    $url = $ia_conf['base_url'] . "buy.php";

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

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        $conn->rollback();
        http_response_code(409);
        echo json_encode(["error" => "Purchase failed"]);
        exit;
    }

    $ia_data = json_decode($response, true);

    // ----------------------------------------------
    // Save order item
    // ----------------------------------------------
    $stmt = $conn->prepare(
        "INSERT INTO mse_order_items
         (order_id, ia_name, ia_item_id, quantity, ia_order_ref)
         VALUES (?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "isiis",
        $order_id,
        $item['ia_name'],
        $item['ia_item_id'],
        $item['quantity'],
        $ia_data['order_id']
    );

    $stmt->execute();
}

// --------------------------------------------------
// 5. Clear cart
// --------------------------------------------------
$stmt = $conn->prepare("DELETE FROM mse_carts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

// --------------------------------------------------
// 6. Commit transaction
// --------------------------------------------------
$conn->commit();

// --------------------------------------------------
// 7. Respond OK
// --------------------------------------------------
echo json_encode([
    "success"  => true,
    "order_id" => $order_id
]);
