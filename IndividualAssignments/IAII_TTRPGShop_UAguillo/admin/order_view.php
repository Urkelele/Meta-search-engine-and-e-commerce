<?php
include "../includes/header.php";
include "../includes/auth.php";
require_admin();
include "../includes/database.php";

// Check order ID
if (!isset($_GET['id'])) {
    echo "Order not found.";
    include "../includes/footer.php";
    exit;
}

$id = intval($_GET['id']);

// Order details
$stmt = $conn->prepare("
    SELECT orders.*, users.email 
    FROM orders 
    JOIN users ON orders.user_id = users.id
    WHERE orders.id=?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "Order not found.";
    include "../includes/footer.php";
    exit;
}
?>

<h2>Order #<?= $order['id'] ?></h2>
<p><b>User:</b> <?= htmlspecialchars($order['email']) ?></p>
<p><b>Date:</b> <?= $order['date_created'] ?></p>
<p><b>Status:</b> <?= $order['status'] ?></p>

<h3>Items in this order:</h3>

<table border="1" cellpadding="8">
<tr>
    <th>Item</th>
    <th>Price</th>
    <th>Quantity</th>
</tr>

<?php
// Order items
$stmt = $conn->prepare("
    SELECT order_items.*, items.name
    FROM order_items
    JOIN items ON order_items.item_id = items.id
    WHERE order_id=?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

while ($item = $result->fetch_assoc()):
?>
<tr>
    <td><?= htmlspecialchars($item['name']) ?></td>
    <td><?= $item['purchase_price'] ?></td>
    <td><?= $item['quantity'] ?></td>
</tr>
<?php endwhile; ?>
</table>

<br>
<a href="orders.php">← Back to Orders</a>

<?php include "../includes/footer.php"; ?>
