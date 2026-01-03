<?php
require_once __DIR__ . "/auth.php";
require_api_key();

require_once __DIR__ . "/../DataBaseManagement/DB.php";

$db = DB::get();
$q = trim($_GET['q'] ?? '');

// Search products
$sql = "
    SELECT p.product_id, p.name, p.description, p.price, p.shipping_price,
           p.available_stock, c.name AS category
    FROM products p
    JOIN categories c ON c.category_id = p.category_id
    WHERE (? = '' OR p.name LIKE CONCAT('%', ?, '%') OR p.description LIKE CONCAT('%', ?, '%'))
    ORDER BY p.created_at DESC
    LIMIT 50
";

$stmt = $db->prepare($sql);
$stmt->bind_param("sss", $q, $q, $q);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while ($row = $res->fetch_assoc()) {
    $id = (int)$row['product_id'];

    $items[] = [
        "source" => "tech",
        "id" => $id,
        "name" => $row['name'],
        "description" => $row['description'],
        "price" => (float)$row['price'],
        "shipping_price" => (float)$row['shipping_price'],
        "stock" => (int)$row['available_stock'],
        "category" => $row['category'],
        // poner una ruta que funcione desde el navegador (esto va?):
        "image" => "/IndividualAssignments/IAII_TechShop_AGarcia/media/productsImages/Product{$id}.jpg",
        "properties" => [] //se llena en item.php
    ];
}

echo json_encode(["items" => $items], JSON_UNESCAPED_UNICODE);
