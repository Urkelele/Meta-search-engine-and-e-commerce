<?php
include "../includes/header.php";
include "../includes/database.php";

// Check item ID
if (!isset($_GET['id'])) {
    echo "<p>Item not found.</p>";
    include "../includes/footer.php";
    exit;
}

$id = intval($_GET['id']);

// Get item details
$stmt = $conn->prepare("
    SELECT items.*, categories.name AS cat_name 
    FROM items 
    JOIN categories ON items.category_id = categories.id 
    WHERE items.id=?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    echo "<p>Item not found.</p>";
    include "../includes/footer.php";
    exit;
}

// Get item category properties
$props = null;

if ($item['category_id'] == 1) {
    $stmt_props = $conn->prepare("SELECT * FROM book_properties WHERE item_id=?");
}
if ($item['category_id'] == 2) {
    $stmt_props = $conn->prepare("SELECT * FROM dice_properties WHERE item_id=?");
}
if ($item['category_id'] == 3) {
    $stmt_props = $conn->prepare("SELECT * FROM mini_properties WHERE item_id=?");
}
    $stmt_props->bind_param("i", $id);
    $stmt_props->execute();
    $props = $stmt_props->get_result()->fetch_assoc();
?>

<h2><?= htmlspecialchars($item['name']) ?></h2>

<!-- Image -->
<?php if ($item['image_path']): ?>
    <img src="../uploads/<?= $item['image_path'] ?>" width="300" height="300" loading="lazy" alt="<?= htmlspecialchars($item['name']) ?>"><br><br>
<?php endif; ?>

<!-- Details -->
<p><b>Category:</b> <?= $item['cat_name'] ?></p>
<p><b>Price:</b> <?= $item['price'] ?>€</p>
<p><b>Stock available:</b> <?= $item['stock'] ?></p>
<p><b>Shipping cost:</b> <?= $item['shipping_cost'] ?>€</p>

<p><b>Description:</b><br>
<?= nl2br(htmlspecialchars($item['description'])) ?></p>

<hr>

<!-- Category details -->
<h3>Item Properties</h3>

<?php if ($item['category_id'] == 1): ?>
    <p><b>System:</b> <?= htmlspecialchars($props['system']) ?></p>
    <p><b>Type:</b> <?= htmlspecialchars($props['type']) ?></p>
    <p><b>Format:</b> <?= htmlspecialchars($props['format']) ?></p>

<?php elseif ($item['category_id'] == 2): ?>
    <p><b>Material:</b> <?= htmlspecialchars($props['material']) ?></p>
    <p><b>Dice Count:</b> <?= htmlspecialchars($props['dice_count']) ?></p>
    <p><b>Theme:</b> <?= htmlspecialchars($props['theme']) ?></p>

<?php elseif ($item['category_id'] == 3): ?>
    <p><b>Size:</b> <?= htmlspecialchars($props['size']) ?></p>
    <p><b>Creature Type:</b> <?= htmlspecialchars($props['creature_type']) ?></p>
    <p><b>Material:</b> <?= htmlspecialchars($props['material']) ?></p>

<?php endif; ?>

<hr>

<!-- Add to cart form -->
<?php if ($item['stock'] > 0): ?>
    <form method="post" action="cart.php">
        <input type="hidden" name="item_id" value="<?= $id ?>">
        <button type="submit">Add to Cart</button>
    </form>
<?php else: ?>
    <p style="color:red;"><b>Out of stock</b></p>
<?php endif; ?>

<br>
<button type="button" onclick="history.back();">← Back to shop</button>

<?php include "../includes/footer.php"; ?>
