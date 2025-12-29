<?php
require_once __DIR__ . '/../DataBaseManagement/DB.php'; // ajusta si tu ruta real es otra
header('Content-Type: application/json; charset=utf-8');

$db = DB::get();

$q = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');     // opcional: "Keyboard"
$attribute = (int)($_GET['attribute_id'] ?? 0); // opcional: attribute_id (subcategoria)
$limit = max(1, min(50, (int)($_GET['limit'] ?? 20)));
$offset = max(0, (int)($_GET['offset'] ?? 0));

$sql = "
SELECT DISTINCT p.product_id, p.name, p.description, p.price, p.shipping_price, p.available_stock,
       c.name AS category_name
FROM products p
JOIN categories c ON c.category_id = p.category_id
LEFT JOIN product_attributes pa ON pa.product_id = p.product_id
WHERE 1=1
";
$params = [];
$types = "";

if ($q !== '') {
  $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
  $types .= "ss";
  $like = "%$q%";
  $params[] = $like;
  $params[] = $like;
}

if ($category !== '') {
  $sql .= " AND c.name = ?";
  $types .= "s";
  $params[] = $category;
}

if ($attribute > 0) {
  $sql .= " AND pa.attribute_id = ?";
  $types .= "i";
  $params[] = $attribute;
}

$sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$types .= "ii";
$params[] = $limit;
$params[] = $offset;

$stmt = $db->prepare($sql);
if (!$stmt) {
  echo json_encode(["success" => false, "error" => $db->error]);
  exit;
}
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while ($row = $res->fetch_assoc()) {
  $items[] = [
    "source" => "tech", // <--- IMPORTANTE (identificador de tu IA)
    "id" => (int)$row['product_id'],
    "name" => $row['name'],
    "description" => $row['description'],
    "price" => (float)$row['price'],
    "shipping_price" => (float)$row['shipping_price'],
    "stock" => (int)$row['available_stock'],
    "category" => $row['category_name'],
    "image" => "/IAII_TechShop_AGarcia/media/productsImages/Product" . (int)$row['product_id'] . ".jpg",
    "properties" => [] // lo llenamos en item.php; aquí lo dejamos vacío para ser ligero
  ];
}

echo json_encode(["success" => true, "items" => $items], JSON_UNESCAPED_UNICODE);
