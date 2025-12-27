<?php
include "../includes/header.php";
include "../includes/auth.php";
require_admin();
include "../includes/database.php";
?>

<h2>Items</h2>
<a href="item_edit.php">+ Add New Item</a><br><br>

<table border="1" cellpadding="8">
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Category</th>
    <th>Price</th>
    <th>Stock</th>
    <th>Actions</th>
</tr>

<?php
// Load items with category names
$sql = "SELECT items.*, categories.name AS cat_name 
        FROM items 
        JOIN categories ON items.category_id = categories.id";

$result = $conn->query($sql);

while ($item = $result->fetch_assoc()):
?>
<tr>
    <td><?= $item['id'] ?></td>
    <td><?= htmlspecialchars($item['name']) ?></td>
    <td><?= htmlspecialchars($item['cat_name']) ?></td>
    <td><?= $item['price'] ?></td>
    <td><?= $item['stock'] ?></td>
    <td>
        <a href="item_edit.php?id=<?= $item['id'] ?>">Edit</a> |
        <a href="item_edit.php?delete=<?= $item['id'] ?>" 
           onclick="return confirm('Are you sure?');">Delete</a>
    </td>
</tr>
<?php endwhile; ?>

</table>

<?php include "../includes/footer.php"; ?>
