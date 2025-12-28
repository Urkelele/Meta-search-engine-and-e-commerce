<?php
header("Content-Type: application/json");

require "../includes/database.php";
require "auth.php"; // 🔐 Protegemos la API

// Get filters
$q = $_GET['q'] ?? "";
$category = $_GET['category'] ?? "";

// Base query
$query = "SELECT items.id, items.name, items.price, items.stock,
                 items.image_path, categories.name AS category
          FROM items
          JOIN categories ON items.category_id = categories.id
          WHERE 1=1";

$params = [];
$types = "";

// Text search
if (!empty($q)) {
    $query .= " AND (items.name LIKE ? OR items.description LIKE ?)";
    $like = "%$q%";
    $params[] = $like;
    $params[] = $like;
    $types .= "ss";
}

// Category filter
if (!empty($category)) {
    $query .= " AND categories.name = ?";
    $params[] = $category;
    $types .= "s";
}

// Prepare
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Build JSON response
$items = [];

while ($row = $result->fetch_assoc()) {
    $items[] = [
        "source"   => "ttrpg",
        "id"       => $row['id'],
        "name"     => $row['name'],
        "price"    => (float)$row['price'],
        "stock"    => (int)$row['stock'],
        "category" => $row['category'],
        "image"    => $row['image_path']
    ];
}

// Output
echo json_encode([
    "success" => true,
    "items"   => $items
]);
