<?php
// Admin protection
if (empty($_SESSION['user']) || $_SESSION['user']['is_admin'] != 1) {
    http_response_code(403);
    exit('Admin only');
}

$db = DB::get();

if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];

    if ($deleteId > 0) {

        $stmt = $db->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param('i', $deleteId);
        $stmt->execute();
        $stmt->close();

        header("Location: ?page=admin_products");
        exit;
    }
}

?>

<h1>Products</h1>

<a href="?page=admin_product_add">+ Add new product</a>

<table border="1" cellpadding="6">
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Price</th>
    <th>Shipping</th>
    <th>Stock</th>
    <th>Actions</th>
</tr>

<?php
$res = $db->query("SELECT product_id, name, price, shipping_price, available_stock FROM products");
while ($p = $res->fetch_assoc()):
?>
<tr>
    <td><?= $p['product_id'] ?></td>
    <td><?= htmlspecialchars($p['name']) ?></td>
    <td><?= number_format($p['price'],2) ?> €</td>
    <td><?= number_format($p['shipping_price'],2) ?> €</td>
    <td><?= $p['available_stock'] ?></td>
    <td>
        <a href="?page=admin_product_add&id=<?= $p['product_id'] ?>">Edit</a>
        |
        <a href="?page=admin_products&delete=<?= $p['product_id'] ?>"
           onclick="return confirm('Delete product?')">Delete</a>
    </td>
</tr>
<?php endwhile; ?>
</table>
