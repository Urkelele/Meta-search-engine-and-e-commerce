<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/auth.php";
require_api_key();

require_once __DIR__ . "/../DataBaseManagement/DB.php";
$db = DB::get();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Missing id"]);
    exit;
}

// Product main data
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
    echo json_encode(["success" => false, "error" => "Not found"]);
    exit;
}

// Product subcategories / attributes
$properties = [];
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
while ($r = $pr->fetch_assoc()) {
    $properties[] = $r["name"];
}

// Product image (I think i doesn't work yet)
$img = null;
$baseDisk = realpath(__DIR__ . "/../media/productsImages");
$scriptDir = rtrim(dirname($_SERVER["SCRIPT_NAME"]), "/"); // .../api
$rootDir   = preg_replace('~/api$~', '', $scriptDir);      // ... (adds Meta-search-engine-and-e-commerce)
$baseUrl   = $rootDir . "/media/productsImages";
$baseName = "Product{$id}";
$exts = ["jpg", "jpeg", "png", "gif", "webp"];

if ($baseDisk) {
    foreach ($exts as $ext) {
        $candidate = $baseDisk . DIRECTORY_SEPARATOR . $baseName . "." . $ext;
        if (is_file($candidate)) {
            $img = $baseUrl . "/" . $baseName . "." . $ext;
            break;
        }
    }
}

// fallback (por si no hay imagen)
if (!$img) {
    $img = $baseUrl . "/no-image.png"; // tendiamos que meter esta imagen
}

// Output json
echo json_encode([
    "success" => true,
    "item" => [
        "source"         => "tech",
        "id"             => (int)$row["product_id"],
        "name"           => (string)$row["name"],
        "description"    => (string)$row["description"],
        "price"          => (float)$row["price"],
        "shipping_price" => (float)$row["shipping_price"],
        "stock"          => (int)$row["available_stock"],
        "category"       => (string)$row["category"],
        "image"          => $img,
        "properties"     => $properties
    ]
], JSON_UNESCAPED_UNICODE);
