<?php
header("Content-Type: application/json");

require "../includes/database.php";
require "auth.php"; // 🔐 API authentication

// Only GET allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

// Get item ID
$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($item_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid item id"]);
    exit;
}

// --------------------------------------------------
// 1. Get base item data
// --------------------------------------------------
$stmt = $conn->prepare(
    "SELECT items.id, items.name, items.description, items.price,
            items.stock, items.image_path, categories.name AS category,
            items.category_id
     FROM items
     JOIN categories ON items.category_id = categories.id
     WHERE items.id = ?"
);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Item not found"]);
    exit;
}

$item = $result->fetch_assoc();

// --------------------------------------------------
// 2. Get category-specific properties
// --------------------------------------------------
$properties = [];

switch ($item['category_id']) {

    case 1: // Books
        $stmt = $conn->prepare(
            "SELECT system, type, format FROM book_properties WHERE item_id = ?"
        );
        $stmt->bind_param("i", $item_id);
        break;

    case 2: // Dice sets
        $stmt = $conn->prepare(
            "SELECT material, dice_count, theme FROM dice_properties WHERE item_id = ?"
        );
        $stmt->bind_param("i", $item_id);
        break;

    case 3: // Miniatures
        $stmt = $conn->prepare(
            "SELECT size, creature_type, material FROM mini_properties WHERE item_id = ?"
        );
        $stmt->bind_param("i", $item_id);
        break;

    default:
        $stmt = null;
}

if ($stmt) {
    $stmt->execute();
    $prop_result = $stmt->get_result();
    if ($prop_result->num_rows > 0) {
        $properties = $prop_result->fetch_assoc();
    }
}

// --------------------------------------------------
// 3. Output JSON
// --------------------------------------------------
echo json_encode([
    "success" => true,
    "item" => [
        "source"     => "ttrpg",
        "id"         => (int)$item['id'],
        "name"       => $item['name'],
        "description"=> $item['description'],
        "price"      => (float)$item['price'],
        "shipping_price" => 0,
        "stock"      => (int)$item['stock'],
        "category"   => $item['category'],
        "image"      => $item['image_path'],
        "properties" => $properties
    ]
]);
