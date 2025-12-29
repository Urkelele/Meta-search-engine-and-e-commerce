<?php
require_once __DIR__ . "/_auth.php";
require_api_key();

require_once __DIR__ . "/../DataBaseManagement/DB.php";
$db = DB::get();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Missing id"]);
    exit;
}

$stmt = $db->prepare("
    SELECT p.product_id, p.name, p.description, p.price, p.shipping_price,
           p.available_stock, c.name AS category
    FROM products p
    JOIN categories c ON c.category_id = p.category_id
    WHERE p.product_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    http_response_code(404);
    echo json_encode(["error" => "Not found"]);
    exit;
}

// properties (atributos / subcategorías del producto)
$props = [];
$ps = $db->prepare("
    SELECT a.name
    FROM product_attributes pa
    JOIN attributes a ON a.attribute_id = pa.attribute_id
    WHERE pa.product_id = ?
    ORDER BY a.name
");
$ps->bind_param("i", $id);
$ps->execute();
$pr = $ps->get_result();
while ($r = $pr->fetch_assoc()) $props[] = $r['name'];

echo json_encode([
    "source" => "tech",
    "id" => (int)$row['product_id'],
    "name" => $row['name'],
    "description" => $row['description'],
    "price" => (float)$row['price'],
    "shipping_price" => (float)$row['shipping_price'],
    "stock" => (int)$row['available_stock'],
    "category" => $row['category'],
    "image" => "/IndividualAssignments/IAII_TechShop_AGarcia/media/productsImages/Product{$id}.jpg",
    "properties" => $props
], JSON_UNESCAPED_UNICODE);
