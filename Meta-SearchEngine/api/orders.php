<?php
// mse/api/orders.php
session_start();
header("Content-Type: application/json; charset=utf-8");

require __DIR__ . "/../includes/db.php";      // $conn (mysqli)
$ias = require __DIR__ . "/../includes/ia_config.php";

$userId = (int)($_SESSION['user']['id'] ?? 0);
if (!$userId) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit;
}

// 1) obtener pedidos + items de la BD
$sql = "
    SELECT o.id AS order_id, o.created_at,
           i.ia_name, i.ia_item_id, i.quantity, i.price_at_purchase, i.ia_order_ref
    FROM mse_orders o
    JOIN mse_order_items i ON o.id = i.order_id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC, o.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
}
$stmt->close();

if (!$rows) {
    echo json_encode(["success" => true, "orders" => []]);
    exit;
}

// 2) función para obtener nombre del item desde la IA
function fetchItemName($iaName, $itemId, $ias) {
    if (!isset($ias[$iaName])) return "Unknown IA";

    $url = $ias[$iaName]['base_url'] . "item.php?id=" . urlencode($itemId);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["X-API-KEY: " . $ias[$iaName]['api_key']],
        CURLOPT_TIMEOUT => 10
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200 || !$resp) return "Unknown item";

    $data = json_decode($resp, true);
    $item = $data['item'] ?? $data;

    return $item['name'] ?? "Unnamed item";
}

// 3) agrupar por pedido y añadir nombre de item
$orders = [];  // order_id => [order_id, created_at, items => []]

foreach ($rows as $r) {
    $oid = $r['order_id'];
    if (!isset($orders[$oid])) {
        $orders[$oid] = [
            "order_id"   => (int)$oid,
            "created_at" => $r['created_at'],
            "items"      => []
        ];
    }

    $itemName = fetchItemName($r['ia_name'], $r['ia_item_id'], $ias);
    $orders[$oid]["items"][] = [
        "ia_name"         => $r['ia_name'],
        "ia_item_id"      => (int)$r['ia_item_id'],
        "name"            => $itemName,
        "quantity"        => (int)$r['quantity'],
        "price_at_purchase" => (float)$r['price_at_purchase'],
        "total"           => (float)$r['price_at_purchase'] * (int)$r['quantity'],
        "ia_order_ref"    => $r['ia_order_ref'],
    ];
}

// 4) devolver JSON
echo json_encode([
    "success" => true,
    "orders"  => array_values($orders) // para que sea un array plano
]);
