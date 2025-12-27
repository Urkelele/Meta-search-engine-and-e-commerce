<?php
include "../includes/header.php";
include "../includes/auth.php";
require_admin();
include "../includes/utils.php";
include "../includes/database.php";

// Item Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Delete categories properties first
    $conn->query("DELETE FROM book_properties WHERE item_id=$id");
    $conn->query("DELETE FROM dice_properties WHERE item_id=$id");
    $conn->query("DELETE FROM mini_properties WHERE item_id=$id");

    // Delete item
    $conn->query("DELETE FROM items WHERE id=$id");

    header("Location: items.php");
    exit;
}

// Item Edit / Add
$id = $_GET['id'] ?? null;
$item = null;
$props = null;

if ($id) {
    // Load item data
    $stmt = $conn->prepare("SELECT * FROM items WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();

    // Load category properties
    if ($item['category_id'] == 1) {
        $props = $conn->query("SELECT * FROM book_properties WHERE item_id=$id")->fetch_assoc();
    } elseif ($item['category_id'] == 2) {
        $props = $conn->query("SELECT * FROM dice_properties WHERE item_id=$id")->fetch_assoc();
    } else {
        $props = $conn->query("SELECT * FROM mini_properties WHERE item_id=$id")->fetch_assoc();
    }
}

// Save item
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'];
    $cat = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $weight = $_POST['weight'];
    $shipping = $_POST['shipping'];
    $desc = $_POST['description'];

    // Image upload
    $image_path = $item['image_path'] ?? null;

    if (!empty($_FILES['image']['name'])) {
        $filename = time() . "_" . basename($_FILES['image']['name']); // Unique filename
        $target = "../uploads/" . $filename;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        $image_path = $filename;
    }

    if ($id) {
        // UPDATE
        $stmt = $conn->prepare("UPDATE items SET name=?, category_id=?, price=?, stock=?, weight=?, shipping_cost=?, description=?, image_path=? WHERE id=?");
        $stmt->bind_param("siddddssi", $name, $cat, $price, $stock, $weight, $shipping, $desc, $image_path, $id);
        $stmt->execute();

    } else {
        // INSERT
        $stmt = $conn->prepare("INSERT INTO items (name, category_id, price, stock, weight, shipping_cost, description, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siddddss", $name, $cat, $price, $stock, $weight, $shipping, $desc, $image_path);
        $stmt->execute();

        $id = $conn->insert_id;
    }

    // Save category properties

    if ($cat == 1) { // Book
        $system = $_POST['system'];
        $type = $_POST['type'];
        $format = $_POST['format'];

        $conn->query("DELETE FROM book_properties WHERE item_id=$id");
        $stmt = $conn->prepare("INSERT INTO book_properties (item_id, system, type, format) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $id, $system, $type, $format);
        $stmt->execute();
    }

    if ($cat == 2) { // Dice sets
        $material = $_POST['material'];
        $count = $_POST['dice_count'];
        $theme = $_POST['theme'];

        $conn->query("DELETE FROM dice_properties WHERE item_id=$id");
        $stmt = $conn->prepare("INSERT INTO dice_properties (item_id, material, dice_count, theme) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $id, $material, $count, $theme);
        $stmt->execute();
    }

    if ($cat == 3) { // Miniatures
        $size = $_POST['size'];
        $ctype = $_POST['creature'];
        $mat = $_POST['minimaterial'];

        $conn->query("DELETE FROM mini_properties WHERE item_id=$id");
        $stmt = $conn->prepare("INSERT INTO mini_properties (item_id, size, creature_type, material) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $id, $size, $ctype, $mat);
        $stmt->execute();
    }

    header("Location: items.php");
    exit;
}

// HTML Form
//I dont know if its considered cleaner to do the forms in echo statements
// or just close the php tags and write html normally so I chose the latter.
?>

<h2><?= $id ? "Edit Item" : "Add New Item" ?></h2>

<form method="post" enctype="multipart/form-data">

Name:<br>
<input type="text" name="name" value="<?= $item['name'] ?? '' ?>" required><br><br>

Category:<br>
<select name="category" required>
    <?php
    $cats = $conn->query("SELECT * FROM categories");
    while ($c = $cats->fetch_assoc()):
    ?>
        <option value="<?= $c['id'] ?>" 
            <?= (isset($item['category_id']) && $item['category_id'] == $c['id']) ? "selected" : "" ?>>
            <?= $c['name'] ?>
        </option>
    <?php endwhile; ?>
</select>
<br><br>

Price:<br>
<input type="number" step="0.01" name="price" value="<?= $item['price'] ?? '' ?>" required><br><br>

Stock:<br>
<input type="number" name="stock" value="<?= $item['stock'] ?? '' ?>" required><br><br>

Weight:<br>
<input type="number" step="0.01" name="weight" value="<?= $item['weight'] ?? '' ?>"><br><br>

Shipping Cost:<br>
<input type="number" step="0.01" name="shipping" value="<?= $item['shipping_cost'] ?? '' ?>"><br><br>

Description:<br>
<textarea name="description" rows="5" cols="50"><?= $item['description'] ?? '' ?></textarea><br><br>

Image:<br>
<input type="file" name="image"><br>

<?php if (!empty($item['image_path'])): ?>
    <img src="../uploads/<?= $item['image_path'] ?>" width="120"><br>
<?php endif; ?>

<hr>

<?php
// Category properties

$selected_cat = $item['category_id'] ?? 1;// Default to Books for new items

// Books
if ($selected_cat == 1 || !$id):
?>
<h3>Book Properties</h3>
System:<br><input type="text" name="system" value="<?= $props['system'] ?? '' ?>"><br><br>
Type:<br><input type="text" name="type" value="<?= $props['type'] ?? '' ?>"><br><br>
Format:<br><input type="text" name="format" value="<?= $props['format'] ?? '' ?>"><br><br>

<?php endif; ?>

<?php
// Dice
if ($selected_cat == 2):
?>
<h3>Dice Properties</h3>
Material:<br><input type="text" name="material" value="<?= $props['material'] ?? '' ?>"><br><br>
Dice Count:<br><input type="number" name="dice_count" value="<?= $props['dice_count'] ?? '' ?>"><br><br>
Theme:<br><input type="text" name="theme" value="<?= $props['theme'] ?? '' ?>"><br><br>

<?php endif; ?>

<?php
// Minis
if ($selected_cat == 3):
?>
<h3>Miniatures Properties</h3>
Size:<br><input type="text" name="size" value="<?= $props['size'] ?? '' ?>"><br><br>
Creature Type:<br><input type="text" name="creature" value="<?= $props['creature_type'] ?? '' ?>"><br><br>
Material:<br><input type="text" name="minimaterial" value="<?= $props['material'] ?? '' ?>"><br><br>

<?php endif; ?>

<button type="submit">Save</button>

</form>

<?php include "../includes/footer.php"; ?>
