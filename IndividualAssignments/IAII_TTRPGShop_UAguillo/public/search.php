<?php
include "../includes/header.php";
include "../includes/database.php";

// Config
$items_per_page = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $items_per_page;

// Get filters
$search_text = $_GET['q'] ?? "";
$category = $_GET['category'] ?? "";
$order = $_GET['order'] ?? "";

// Base query
$query = "SELECT items.*, categories.name AS cat_name FROM items 
          JOIN categories ON items.category_id = categories.id
          WHERE 1=1";

$params = [];
$types = "";

// Text search
if (!empty($search_text)) {
    $query .= " AND (items.name LIKE ? OR items.description LIKE ?)";
    $like = "%$search_text%";
    $params[] = $like;
    $params[] = $like;
    $types .= "ss";
}

// Category filter
if (!empty($category)) {
    $query .= " AND category_id = ?";
    $params[] = $category;
    $types .= "i";
}

// Category-specific filters
if ($category == 1) { // Books
    if (isset($_GET['type'])) {
        $query .= " AND items.id IN (SELECT item_id FROM book_properties WHERE type LIKE ?)";
        $params[] = "%" . $_GET['type'] . "%";
        $types .= "s";
    }
    if (isset($_GET['system'])) {
        $query .= " AND items.id IN (SELECT item_id FROM book_properties WHERE system LIKE ?)";
        $params[] = "%" . $_GET['system'] . "%";
        $types .= "s";
    }
    if (isset($_GET['format'])) {
        $query .= " AND items.id IN (SELECT item_id FROM book_properties WHERE format LIKE ?)";
        $params[] = "%" . $_GET['format'] . "%";
        $types .= "s";
    }
}

if ($category == 2) { // Dice Sets
    if (isset($_GET['material'])) {
        $query .= " AND items.id IN (SELECT item_id FROM dice_properties WHERE material LIKE ?)";
        $params[] = "%" . $_GET['material'] . "%";
        $types .= "s";
    }
    if (isset($_GET['dice_count'])) {
        $query .= " AND items.id IN (SELECT item_id FROM dice_properties WHERE dice_count = ?)";
        $params[] = $_GET['dice_count'];
        $types .= "i";
    }
    if (isset($_GET['theme'])) {
        $query .= " AND items.id IN (SELECT item_id FROM dice_properties WHERE theme LIKE ?)";
        $params[] = "%" . $_GET['theme'] . "%";
        $types .= "s";
    }
}

if ($category == 3) { // Miniatures
    if (isset($_GET['size'])) {
        $query .= " AND items.id IN (SELECT item_id FROM mini_properties WHERE size LIKE ?)";
        $params[] = "%" . $_GET['size'] . "%";
        $types .= "s";
    }
    if (isset($_GET['creature'])) {
        $query .= " AND items.id IN (SELECT item_id FROM mini_properties WHERE creature_type LIKE ?)";
        $params[] = "%" . $_GET['creature'] . "%";
        $types .= "s";
    }
    if (isset($_GET['minimaterial'])) {
        $query .= " AND items.id IN (SELECT item_id FROM mini_properties WHERE material LIKE ?)";
        $params[] = "%" . $_GET['minimaterial'] . "%";
        $types .= "s";
    }
}

// Ordering
if ($order == "price_asc") {
    $query .= " ORDER BY items.price ASC";
} elseif ($order == "price_desc") {
    $query .= " ORDER BY items.price DESC";
}

// Save base query for pagination count
$base_query = $query;

// Pagination
$query .= " LIMIT $items_per_page OFFSET $offset";

// Prepare and execute
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Item Search</h2>
<form method="get" action="search.php">

    <!-- Search bar -->
    Search: 
    <input type="text" name="q" value="<?= htmlspecialchars($search_text) ?>">

    <!-- Categories -->
    <select name="category" onchange="this.form.submit()">
        <option value="">All Categories</option>
        <?php
        $cats = $conn->query("SELECT * FROM categories");
        while ($cat = $cats->fetch_assoc()):
        ?>
            <option value="<?= $cat['id'] ?>"
                <?= ($category == $cat['id']) ? "selected" : "" ?>>
                <?= $cat['name'] ?>
            </option>
        <?php endwhile; ?>
    </select>

    <br><br>

    <!-- Category Properties -->
    <?php if ($category == 1): ?>
        System: <input type="text" name="system" value="<?= $_GET['system'] ?? '' ?>"><br><br>
        Type: <input type="text" name="type" value="<?= $_GET['type'] ?? '' ?>"><br><br>
        Format: <input type="text" name="format" value="<?= $_GET['format'] ?? '' ?>"><br><br>
    <?php endif; ?>

    <?php if ($category == 2): ?>
        Material: <input type="text" name="material" value="<?= $_GET['material'] ?? '' ?>"><br><br>
        Dice Count: <input type="number" name="dice_count" value="<?= $_GET['dice_count'] ?? '' ?>"><br><br>
        Theme: <input type="text" name="theme" value="<?= $_GET['theme'] ?? '' ?>"><br><br>
    <?php endif; ?>

    <?php if ($category == 3): ?>
        Size: <input type="text" name="size" value="<?= $_GET['size'] ?? '' ?>"><br><br>
        Creature Type: <input type="text" name="creature" value="<?= $_GET['creature'] ?? '' ?>"><br><br>
        Material: <input type="text" name="minimaterial" value="<?= $_GET['minimaterial'] ?? '' ?>"><br><br>
    <?php endif; ?>

    Order By:
    <select name="order">
        <option value="">Default</option>
        <option value="price_asc"  <?= ($order == "price_asc") ? "selected" : "" ?>>Price Low → High</option>
        <option value="price_desc" <?= ($order == "price_desc") ? "selected" : "" ?>>Price High → Low</option>
    </select>

    <br><br>
    <button type="submit">Apply</button>
</form>

<hr>

<h3>Results:</h3>
<?php while ($item = $result->fetch_assoc()): ?>
<div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">

    <!-- Image -->
    <?php if ($item['image_path']): ?>
        <img src="../uploads/<?= $item['image_path'] ?>" width="120" style="float:left; margin-right:10px;">
    <?php endif; ?>

    <!-- Name -->
    <h3>
        <a href="item.php?id=<?= $item['id'] ?>">
            <?= htmlspecialchars($item['name']) ?>
        </a>
    </h3>

    <!-- Cat + price -->
    <p><?= $item['cat_name'] ?> · <b><?= $item['price'] ?>€</b></p>

    <!-- Description -->
    <p><?= substr(htmlspecialchars($item['description']), 0, 80) ?>...</p>

    <div style="clear:both;"></div>
</div>
<?php endwhile; ?>

<hr>

<?php
// Pagination count
$count_query = "SELECT COUNT(*) AS total FROM ($base_query) AS filtered_items";
$count_stmt = $conn->prepare($count_query);

if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}

$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_items = $count_result->fetch_assoc()['total'];

$total_pages = ceil($total_items / $items_per_page);
?>

<div>
    Pages:
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>

        <?php
        $query_params = $_GET;
        $query_params['page'] = $i;
        $url = "search.php?" . http_build_query($query_params);
        ?>

        <a href="<?= $url ?>"><?= $i ?></a>

    <?php endfor; ?>
</div>

<?php include "../includes/footer.php"; ?>
