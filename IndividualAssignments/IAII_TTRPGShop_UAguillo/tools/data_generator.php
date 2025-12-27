<?php
// Data Generator Script

include "../includes/database.php";

echo "<h2>Data Generator</h2>";

// Configuration
$num_users = 10;
$num_items_per_cat = 6;
$num_orders = 5;

//Coment this block to avoid accidental data deletion

// Disable foreign key checks (required to truncate linked tables)
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Order matters because of foreign-key dependencies
$conn->query("TRUNCATE TABLE order_items");
$conn->query("TRUNCATE TABLE orders");

$conn->query("TRUNCATE TABLE book_properties");
$conn->query("TRUNCATE TABLE dice_properties");
$conn->query("TRUNCATE TABLE mini_properties");

$conn->query("TRUNCATE TABLE items");

$conn->query("TRUNCATE TABLE users");

// Re-enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

echo "<p>✓ All tables cleared.</p><hr>";


//Generate Users
echo "<h3>Generating users...</h3>";

// Admin user
$admin_email = "admin@example.com";
$admin_password = password_hash("admin123", PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO users (email, password_hash, confirmed, is_admin)
     VALUES (?, ?, 1, 1)"
);
$stmt->bind_param("ss", $admin_email, $admin_password);
$stmt->execute();

echo "✓ Admin user created: admin@example.com / admin123<br>";

// Regular users
for ($i = 1; $i <= $num_users; $i++) {

    $email = "user$i@example.com";
    $pass = password_hash("password$i", PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO users (email, password_hash, confirmed, is_admin)
         VALUES (?, ?, 1, 0)"
    );
    $stmt->bind_param("ss", $email, $pass);
    $stmt->execute();
}

echo "✓ $num_users users created.<br><br>";


//Generate Items and Properties
echo "<h3>Generating items...</h3>";

$categories = [
    1 => "Books",
    2 => "Dice Sets",
    3 => "Miniatures"
];

function random_word() {
    $words = ["Arcane", "Dragon", "Shadow", "Ancient", "Mystic", "Crystal", "Eternal", "Realm", "Forgotten"];
    return $words[array_rand($words)];
}

for ($cat = 1; $cat <= 3; $cat++) {

    for ($i = 1; $i <= $num_items_per_cat; $i++) {

        // Common item data
        $name = $categories[$cat] . " " . random_word() . " $i";
        $price = rand(5, 80);
        $stock = rand(1, 20);
        $weight = rand(1, 5);
        $shipping = rand(2, 10);
        $desc = "Auto-generated description for $name.";

        $stmt = $conn->prepare("
            INSERT INTO items (name, category_id, price, stock, weight, shipping_cost, description, image_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, '')
        ");
        $stmt->bind_param("siiidds", $name, $cat, $price, $stock, $weight, $shipping, $desc);
        $stmt->execute();

        $item_id = $conn->insert_id;

        // Generate category-specific properties

        // Books
        if ($cat == 1) {
            $systems = ["D&D 5e", "Pathfinder 2e", "Warhammer", "Call of Cthulhu"];
            $types = ["Core Book", "Adventure", "Supplement", "Campaign"];
            $formats = ["Hardcover", "Softcover", "PDF"];

            $system = $systems[array_rand($systems)];
            $type = $types[array_rand($types)];
            $format = $formats[array_rand($formats)];

            $stmt = $conn->prepare("
                INSERT INTO book_properties (item_id, system, type, format)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("isss", $item_id, $system, $type, $format);
            $stmt->execute();
        }

        // Dice
        if ($cat == 2) {
            $materials = ["Acrylic", "Metal", "Resin", "Stone"];
            $themes = ["Fire", "Frost", "Bloodstone", "Galaxy", "Necrotic"];

            $material = $materials[array_rand($materials)];
            $theme = $themes[array_rand($themes)];
            $dice_count = [7, 10, 12][array_rand([7, 10, 12])];

            $stmt = $conn->prepare("
                INSERT INTO dice_properties (item_id, material, dice_count, theme)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("isis", $item_id, $material, $dice_count, $theme);
            $stmt->execute();
        }

        // Minis
        if ($cat == 3) {
            $sizes = ["Small", "Medium", "Large", "Huge"];
            $types = ["Dragon", "Undead", "Humanoid", "Beast", "Elemental"];
            $materials = ["Plastic", "Resin", "Metal"];

            $size = $sizes[array_rand($sizes)];
            $ctype = $types[array_rand($types)];
            $mat = $materials[array_rand($materials)];

            $stmt = $conn->prepare("
                INSERT INTO mini_properties (item_id, size, creature_type, material)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("isss", $item_id, $size, $ctype, $mat);
            $stmt->execute();
        }
    }
}

echo "✓ Items and properties generated.<br><br>";

//Generate Orders
echo "<h3>Generating orders...</h3>";

$user_ids = [];
$res = $conn->query("SELECT id FROM users");
while ($u = $res->fetch_assoc()) $user_ids[] = $u['id'];

$item_ids = [];
$res = $conn->query("SELECT id, price FROM items");
while ($i = $res->fetch_assoc()) $item_ids[] = $i;

for ($i = 1; $i <= $num_orders; $i++) {

    // Random user
    $uid = $user_ids[array_rand($user_ids)];

    $status = ["paid", "shipped", "pending"][array_rand(["paid", "shipped", "pending"])];
    $stmt = $conn->prepare("INSERT INTO orders (user_id, status) VALUES (?, ?)");
    $stmt->bind_param("is", $uid, $status);
    $stmt->execute();

    $order_id = $conn->insert_id;

    // Random items in order
    $items_in_order = rand(1, 4);

    for ($j = 1; $j <= $items_in_order; $j++) {
        $item = $item_ids[array_rand($item_ids)];

        $qty = rand(1, 3);
        $price = $item['price'];

        $stmt = $conn->prepare("
            INSERT INTO order_items (order_id, item_id, quantity, purchase_price)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiid", $order_id, $item['id'], $qty, $price);
        $stmt->execute();
    }
}

echo "✓ $num_orders random orders created.<br><br>";

echo "<h2>DATA GENERATION COMPLETE ✔️</h2>";

?>
