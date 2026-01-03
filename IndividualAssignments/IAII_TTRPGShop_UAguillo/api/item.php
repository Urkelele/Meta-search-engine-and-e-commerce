<?php
header("Content-Type: application/json; charset=utf-8");

require __DIR__ . "/auth.php";
require_api_key();

require __DIR__ . "/../includes/database.php";

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Missing id"]);
    exit;
}

// obtain item main data
$stmt = $conn->prepare("
    SELECT i.id, i.name, i.description, i.price, i.shipping_cost, i.stock,
           c.name AS category, i.image_path
    FROM items i
    JOIN categories c ON c.id = i.category_id
    WHERE i.id = ?
    LIMIT 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    http_response_code(404);
    echo json_encode(["success" => false, "error" => "Not found"]);
    exit;
}

/**
 * properties: category dependent (Books / Dice Sets / Miniatures)
 * - Books => book_properties
 * - Dice Sets => dice_properties
 * - Miniatures => mini_properties
 */
$properties = [];

if ($item['category'] === 'Books') {
    $ps = $conn->prepare("SELECT system, type, format FROM book_properties WHERE item_id = ? LIMIT 1");
    $ps->bind_param("i", $id);
    $ps->execute();
    $row = $ps->get_result()->fetch_assoc();
    if ($row) {
        foreach ($row as $k => $v) if ($v !== null && $v !== '') $properties[$k] = $v;
    }
} elseif ($item['category'] === 'Dice Sets') {
    $ps = $conn->prepare("SELECT material, dice_count, theme FROM dice_properties WHERE item_id = ? LIMIT 1");
    $ps->bind_param("i", $id);
    $ps->execute();
    $row = $ps->get_result()->fetch_assoc();
    if ($row) {
        foreach ($row as $k => $v) if ($v !== null && $v !== '') $properties[$k] = $v;
    }
} elseif ($item['category'] === 'Miniatures') {
    $ps = $conn->prepare("SELECT size, creature_type, material FROM mini_properties WHERE item_id = ? LIMIT 1");
    $ps->bind_param("i", $id);
    $ps->execute();
    $row = $ps->get_result()->fetch_assoc();
    if ($row) {
        foreach ($row as $k => $v) if ($v !== null && $v !== '') $properties[$k] = $v;
    }
}

$imagePath = $item['image_path'] ?? '';
$scriptDir = rtrim(dirname($_SERVER["SCRIPT_NAME"]), "/"); // .../api
$rootDir   = preg_replace('~/api$~', '', $scriptDir);      // ...
$imageUrl  = $imagePath !== '' ? ($rootDir . "/uploads/" . $imagePath) : "";

echo json_encode([
    "success" => true,
    "item" => [
        "source" => "ttrpg",
        "id" => (int)$item['id'],
        "name" => $item['name'],
        "description" => $item['description'] ?? '',
        "price" => (float)$item['price'],
        "shipping_price" => (float)($item['shipping_cost'] ?? 0),
        "stock" => (int)$item['stock'],
        "category" => $item['category'],
        "image" => $imageUrl,
        "properties" => $properties
    ]
], JSON_UNESCAPED_UNICODE);
