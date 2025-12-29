<?php 
// Admin protection
if (empty($_SESSION['user']) || $_SESSION['user']['is_admin'] != 1) {
    http_response_code(403);
    exit('Admin only');
}

$db = DB::get();

if (isset($_GET['ship'])) {
    $stmt = $db->prepare("UPDATE orders SET status='shipped' WHERE order_id=?");
    $stmt->bind_param('i', $_GET['ship']);
    $stmt->execute();
}

if (isset($_GET['delete'])) {
    $orderId = (int)$_GET['delete'];

    if ($orderId > 0) {
        $stmt = $db->prepare("DELETE FROM orders WHERE order_id = ?");
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $stmt->close();

        header("Location: ?page=admin_orders");
        exit;
    }
}

?>

<h1>Orders</h1>

<table border="1" cellpadding="6">
<tr>
    <th>ID</th>
    <th>User ID</th>
    <th>User email</th>
    <th>Total</th>
    <th>Status</th>
    <th>Actions</th>
</tr>

<?php
$res = $db->query("
    SELECT o.order_id, u.user_id ,u.email, o.total_price, o.status
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
");

while ($o = $res->fetch_assoc()):
?>
<tr>
    <td><?= $o['order_id'] ?></td>
    <td><?= htmlspecialchars($o['user_id']) ?></td>
    <td><?= htmlspecialchars($o['email']) ?></td>
    <td>€<?= number_format($o['total_price'],2) ?></td>
    <td><?= $o['status'] ?></td>
    <td>
        <a href="?page=admin_orders&ship=<?= $o['order_id'] ?>">Mark sent</a>
        |
        <a href="?page=admin_orders&delete=<?= $o['order_id'] ?>">Delete</a>
    </td>
</tr>
<?php endwhile; ?>
</table>
