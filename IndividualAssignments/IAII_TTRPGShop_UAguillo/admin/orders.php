<?php
include "../includes/header.php";
include "../includes/auth.php";
require_admin();
include "../includes/database.php";

// Manage order status
if (isset($_GET['setstatus']) && isset($_GET['id'])) {
    $status = $_GET['setstatus'];
    $id = intval($_GET['id']);

    // Validate status
    $valid = ['pending', 'paid', 'shipped', 'cancelled'];
    if (in_array($status, $valid)) {

        $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
    }

    header("Location: orders.php");
    exit;
}
?>

<h2>Orders</h2>

<table border="1" cellpadding="8">
<tr>
    <th>ID</th>
    <th>User</th>
    <th>Date</th>
    <th>Status</th>
    <th>Items</th>
    <th>Actions</th>
</tr>

<?php
// Load orders with user emails
$sql = "SELECT orders.*, users.email 
        FROM orders 
        JOIN users ON orders.user_id = users.id
        ORDER BY orders.date_created DESC";

$result = $conn->query($sql);

while ($order = $result->fetch_assoc()):
?>
<tr>
    <td><?= $order['id'] ?></td>
    <td><?= htmlspecialchars($order['email']) ?></td>
    <td><?= $order['date_created'] ?></td>
    <td><?= $order['status'] ?></td>
    <td>
        <a href="order_view.php?id=<?= $order['id'] ?>">View</a>
    </td>
    <td>
        <a href="orders.php?id=<?= $order['id'] ?>&setstatus=shipped">Mark Shipped</a> |
        <a href="orders.php?id=<?= $order['id'] ?>&setstatus=cancelled"
           onclick="return confirm('Cancel this order?');">Cancel</a>
    </td>
</tr>
<?php endwhile; ?>
</table>

<?php include "../includes/footer.php"; ?>
