<?php
require_once __DIR__ . "/_auth.php";
require_api_key();

require_once __DIR__ . "/../DataBaseManagement/DB.php";

$db = DB::get();
$q = trim($_GET['q'] ?? '');

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
        "source" => "tech", // opcional, no molesta
        "id" => $id,
        "name" => $row['name'],
        "description" => $row['description'],
        "price" => (float)$row['price'],
        "shipping_price" => (float)$row['shipping_price'],
        "stock" => (int)$row['available_stock'],
        "category" => $row['category'],
        // pon una ruta que te funcione desde el navegador:
        "image" => "/IndividualAssignments/IAII_TechShop_AGarcia/media/productsImages/Product{$id}.jpg",
        "properties" => [] // si quieres, lo llenas en item.php
    ];
}

echo json_encode(["items" => $items], JSON_UNESCAPED_UNICODE);
