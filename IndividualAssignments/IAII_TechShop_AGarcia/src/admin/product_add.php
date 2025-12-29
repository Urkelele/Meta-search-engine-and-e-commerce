<?php
$db = DB::get();
// Admin protection
if (empty($_SESSION['user']) || $_SESSION['user']['is_admin'] != 1) {
    http_response_code(403);
    exit('Admin only');
}

$id = (int)($_GET['id'] ?? 0);

$product = null;

if ($id) {
    $stmt = $db->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$product) {
        exit('Product not found');
    }
}

// Categories
$categories = $db->query("
    SELECT category_id, name
    FROM categories
")->fetch_all(MYSQLI_ASSOC);

// Subcategories
$subcategories = $db->query("
    SELECT 
        s.attribute_id,
        s.category_id,
        a.name
    FROM subcategories s
    JOIN attributes a ON a.attribute_id = s.attribute_id
")->fetch_all(MYSQLI_ASSOC);


$selectedSubs = [];

if ($id) {
    $stmt = $db->prepare("SELECT attribute_id FROM product_attributes WHERE product_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $selectedSubs[] = (int)$row['attribute_id'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['name'];
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $categoryId = (int)$_POST['category_id'];
    $shipping = (float)$_POST['shipping_price'];

    if ($id) {
        $stmt = $db->prepare("
            UPDATE products
            SET name=?, price=?, shipping_price=?, available_stock=?, category_id=?
            WHERE product_id=?
        ");
        $stmt->bind_param('sddiii', $name, $price, $shipping, $stock, $categoryId, $id);
        $stmt->execute();
    } else {
        $stmt = $db->prepare("
            INSERT INTO products (name, price, shipping_price, available_stock, category_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('sddii', $name, $price, $shipping, $stock, $categoryId);
        $stmt->execute();
        $id = $db->insert_id;
    }

    // Save subcategories
    $stmt = $db->prepare("DELETE FROM product_attributes WHERE product_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    if (!empty($_POST['subcategories'])) {
        $stmt = $db->prepare("
            INSERT INTO product_attributes (product_id, attribute_id)
            VALUES (?, ?)
        ");
        foreach ($_POST['subcategories'] as $subId) {
            $subId = (int)$subId;
            $stmt->bind_param('ii', $id, $subId);
            $stmt->execute();
        }
    }

    // Image upload
    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            __DIR__ . "/../../media/productsImages/Product{$id}.{$ext}"
        );
    }

    header('Location: ?page=admin_products');
    exit;

    
}
?>

<h1><?= $id ? 'Edit' : 'New' ?> product</h1>

<form method="post" enctype="multipart/form-data">
    <label>Name</label><br>
    <input name="name" value="<?= htmlspecialchars($product['name'] ?? '') ?>"><br><br>

    <label>Price</label><br>
    <input type="number" step="0.01" name="price" value="<?= $product['price'] ?? '0.00' ?>"><br><br>

    <label>Shipping price</label><br>
    <input type="number" step="0.01" name="shipping_price"
       value="<?= $product['shipping_price'] ?? '0.00' ?>"><br><br>

    <label>Stock</label><br>
    <input type="number" name="stock" value="<?= $product['available_stock'] ?? 0 ?>"><br><br>

    <label>Image</label><br>
    <input type="file" name="image"><br><br>
    

<label>Category</label><br>
<select name="category_id" id="categorySelect" required>
    <option value="">-- select --</option>
    <?php foreach ($categories as $c): ?>
        <option value="<?= $c['category_id'] ?>">
            <?= ($product && $product['category_id'] == $c['category_id']) ? 'current' : '' ?>  
            <?= htmlspecialchars($c['name']) ?>
        </option>
    <?php endforeach; ?>
</select>

<br><br>
<label>Types</label>
<div id="subcategoriesBox"></div>

<button type="submit" style = "margin-right: 15px;">Save</button>
</form>

<script>
const subcategories = <?= json_encode($subcategories) ?>;
const selected = <?= json_encode($selectedSubs ?? []) ?>;

document.getElementById('categorySelect').addEventListener('change', function () {
    const catId = this.value;
    const box = document.getElementById('subcategoriesBox');
    box.innerHTML = '';

    subcategories.forEach(s => {
        if (s.category_id == catId) {
            const checked = selected.includes(parseInt(s.attribute_id)) ? 'checked' : '';
            box.innerHTML += `
                <label>
                    <input type="checkbox" name="subcategories[]" value="${s.attribute_id}" ${checked}>
                    ${s.name}
                </label><br>
            `;
        }
    });
});

if (<?= (int)$id ?> && <?= (int)($product['category_id'] ?? 0) ?>) {
    document.getElementById('categorySelect').value = <?= (int)$product['category_id'] ?>;
    document.getElementById('categorySelect').dispatchEvent(new Event('change'));
}
</script>