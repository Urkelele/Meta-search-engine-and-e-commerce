<?php
require_once __DIR__ . '/../DataBaseManagement/DB.php';

$db = DB::get();
$db->set_charset('utf8mb4');

$usersNumber   = 30;
$productsNumber = 40;
$ordersNumber  = 50;

function randEmail() {
    return 'user' . rand(1000,9999) . '@mail.com';
}

function randName() {
    $names = ['Pablo','Juan','Laura','Ana','Mario','Lucia','Carlos','Sara','David','Elena'];
    return $names[array_rand($names)];
}


// Insert the users
$stmt = $db->prepare("INSERT INTO users (email, password, name, is_verified, is_admin) VALUES (?, ?, ?, 1, 0)");

for ($i = 0; $i < $usersNumber; $i++) {
    $email = randEmail();
    $pass  = password_hash('123456', PASSWORD_DEFAULT);
    $name  = randName();

    $stmt->bind_param('sss', $email, $pass, $name);
    $stmt->execute();
}

$stmt->close();

// Insert the products with their categories and subcategories

$categories = $db->query("SELECT category_id FROM categories")->fetch_all(MYSQLI_ASSOC);
$attributes = $db->query("SELECT attribute_id FROM attributes")->fetch_all(MYSQLI_ASSOC);

$stmt = $db->prepare("
    INSERT INTO products
    (name, category_id, description, price, shipping_price, available_stock, reserved_stock)
    VALUES (?, ?, ?, ?, ?, ?, 0)
");

for ($i = 0; $i < $productsNumber; $i++) {
    $cat = $categories[array_rand($categories)]['category_id'];

    $name = "Product " . rand(100,999);
    $desc = "Auto generated product description";
    $price = rand(10,300);
    $ship  = rand(3,20);
    $stock = rand(5,50);

    $stmt->bind_param('sisddi', $name, $cat, $desc, $price, $ship, $stock);
    $stmt->execute();

    $productId = $db->insert_id;

    shuffle($attributes);
    $attrCount = rand(1,4);

    for ($j = 0; $j < $attrCount; $j++) {
        $aid = $attributes[$j]['attribute_id'];
        $db->query("INSERT IGNORE INTO product_attributes VALUES ($productId, $aid)");
    }
}

$stmt->close();

// Load users with products
$users = $db->query("SELECT user_id FROM users WHERE is_admin = 0")->fetch_all(MYSQLI_ASSOC);
$products = $db->query("SELECT product_id, price FROM products")->fetch_all(MYSQLI_ASSOC);

for ($i = 0; $i < $ordersNumber; $i++) {

    $db->begin_transaction();

    $userId = $users[array_rand($users)]['user_id'];
    $status = (rand(0,1) ? 'paid' : 'shipped');

    $db->query("
        INSERT INTO orders (user_id, total_price, status)
        VALUES ($userId, 0, '$status')
    ");

    $orderId = $db->insert_id;
    $total = 0;

    shuffle($products);
    $items = rand(1,4);

    for ($j = 0; $j < $items; $j++) {
        $p = $products[$j];
        $qty = rand(1,3);
        $price = $p['price'];

        $db->query("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES ($orderId, {$p['product_id']}, $qty, $price)
        ");

        $total += $qty * $price;
    }

    $db->query("
        UPDATE orders SET total_price = $total WHERE order_id = $orderId
    ");

    $db->commit();
}

echo "All data has been generated successfully:".
"<br>" . $usersNumber . " Users" .
"<br>" . $productsNumber . " Products" .
"<br>" . $ordersNumber ." Orders";