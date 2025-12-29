<?php
$pdo = DB::get();

// Load all categories
$result = $pdo->query("SELECT category_id, name FROM categories ORDER BY name");
$cats = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $cats[] = $row;
    }
}

// Load all subcategories
$result = $pdo->query("SELECT s.attribute_id, a.name, s.category_id FROM subcategories s 
LEFT JOIN attributes a ON s.attribute_id = a.attribute_id ORDER BY name");
$subcats = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $subcats[] = $row;
    }
}

// Get filter parameters from GET
$category = $_GET['category'] ?? null;
$subcategory = $_GET['subcategory'] ?? null;
$q = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'newest';
$pageNum = max(1, (int)($_GET['p'] ?? 1));
$perPage = 12;
$offset = ($pageNum - 1) * $perPage;

// Get all the products
$sql = "SELECT DISTINCT p.*
        FROM products p
        LEFT JOIN product_attributes pa ON pa.product_id = p.product_id
        WHERE 1=1";
$params = [];

if ($category) {
    $sql .= " AND p.category_id = ?";
    $params[] = (int)$category;
}
if ($subcategory) {
    $sql .= " AND pa.attribute_id = ?";
    $params[] = (int)$subcategory;
}
if ($q !== '') {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

$countSql = "SELECT COUNT(DISTINCT p.product_id) as total
             FROM products p
             LEFT JOIN product_attributes pa ON pa.product_id = p.product_id
             WHERE 1=1";
$countParams = [];
if ($category) {
    $countSql .= " AND p.category_id = ?";
    $countParams[] = (int)$category;
}
if ($subcategory) {
    $countSql .= " AND pa.attribute_id = ?";
    $countParams[] = (int)$subcategory;
}
if ($q !== '') {
    $countSql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $countParams[] = "%$q%";
    $countParams[] = "%$q%";
}

$countStmt = $pdo->prepare($countSql);
$countStmt->execute($countParams);
$countResult = $countStmt->get_result();
$countRow = $countResult->fetch_assoc();
$totalProducts = (int)($countRow['total'] ?? 0);
$totalPages = ceil($totalProducts / $perPage);

// Apply sorting
switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY p.name ASC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY p.created_at DESC";
        break;
}

// Fetch products
$sql .= " LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$result = $stmt->get_result();
$products = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<div style="padding: 20px;">
    <h1>Products</h1>

    <form method="get" style="margin-bottom: 20px; padding: 15px; background: #f5f5f5; border-radius: 5px;">
        <input type="hidden" name="page" value="products">
        
        <div style="margin-bottom: 10px;">
            <label for="q">Search:</label>
            <input id="q" name="q" placeholder="Search products..." value="<?= htmlspecialchars($q) ?>" style="padding: 8px; width: 250px;">
        </div>

        <div style="margin-bottom: 10px;">
            <label for="category">Category:</label>
            <select id="category" name="category" style="padding: 8px; width: 200px;">
                <option value="">All Categories</option>
                <?php foreach($cats as $c): ?>
                    <option value="<?= $c['category_id'] ?>" <?= $category == $c['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 10px;">
            <label for="subcategory">Subcategory:</label>
            <select id="subcategory" name="subcategory" style="padding: 8px; width: 200px;">
                <option value="">All Subcategories</option>
                <?php foreach($subcats as $sc): 
                    // Show only subcategories for selected category, or all if no category selected
                    if (!$category || $sc['category_id'] == $category): ?>
                        <option value="<?= $sc['attribute_id'] ?>" <?= $subcategory == $sc['attribute_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sc['name']) ?>
                        </option>
                    <?php endif;
                endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 10px;">
            <label for="sort">Sort by:</label>
            <select id="sort" name="sort" style="padding: 8px; width: 200px;">
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name: A to Z</option>
            </select>
        </div>

        <button type="submit" style="padding: 8px 20px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer;">Filter</button>
        <a href="?page=products" style="padding: 8px 20px; background: #6c757d; color: white; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; display: inline-block;">Clear</a>
    </form>

    <?php if($totalProducts === 0): ?>
        <p style="font-size: 16px; color: #666;">No products found.</p>
    <?php else: ?>
        <p style="margin-bottom: 15px;">Showing <?= count($products) ?> of <?= $totalProducts ?> products (Page <?= $pageNum ?> of <?= $totalPages ?>)</p>

        <div style="display: flex; flex-wrap: wrap; gap: 15px;">
            <?php foreach($products as $p): ?>
                <div style="border: 1px solid #ddd; padding: 15px; width: 200px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h4 style="margin: 0 0 10px 0; min-height: 40px;"><?= htmlspecialchars($p['name'] ?? 'Unnamed') ?></h4>
                    <p style="color: #666; font-size: 14px; margin: 8px 0;"><?= htmlspecialchars(substr($p['description'] ?? '', 0, 50)) ?>...</p>
                    <p style="font-size: 18px; font-weight: bold; color: #28a745; margin: 10px 0;">Price: €<?= number_format($p['price'] ?? 0, 2) ?></p>
                    <p style="font-size:13px;color:#666;margin:4px 0;"> Shipping: €<?= number_format($p['shipping_price'], 2) ?></p>
                    <p style="margin: 10px 0;">
                        <a href="?page=product&id=<?= $p['product_id'] ?>" style="padding: 8px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 3px; cursor: pointer;">View Details</a>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if($totalPages > 1): ?>
            <div style="margin-top: 30px; text-align: center;">
                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if($i == $pageNum): ?>
                        <strong style="padding: 8px 10px; margin: 0 3px;"><?= $i ?></strong>
                    <?php else: ?>
                        <a href="?page=products&p=<?= $i ?><?= $category ? '&category=' . $category : '' ?><?= $subcategory ? '&subcategory=' . $subcategory : '' ?><?= $q ? '&q=' . urlencode($q) : '' ?>" style="padding: 8px 10px; margin: 0 3px; background: #f0f0f0; text-decoration: none; border-radius: 3px;">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
