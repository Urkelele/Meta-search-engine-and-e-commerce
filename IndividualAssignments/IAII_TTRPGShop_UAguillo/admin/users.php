<?php
include "../includes/header.php";
include "../includes/auth.php";
require_admin();
include "../includes/database.php";

// User Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // First delete related orders and order items
    $conn->query("DELETE FROM order_items WHERE order_id IN (SELECT id FROM orders WHERE user_id=$id)");
    $conn->query("DELETE FROM orders WHERE user_id=$id");

    // Then delete the user
    $conn->query("DELETE FROM users WHERE id=$id");

    header("Location: users.php");
    exit;
}
?>

<h2>Users</h2>

<table border="1" cellpadding="8">
<tr>
    <th>ID</th>
    <th>Email</th>
    <th>Confirmed</th>
    <th>Actions</th>
</tr>

<?php
$result = $conn->query("SELECT * FROM users ORDER BY id DESC");

while ($user = $result->fetch_assoc()):
?>
<tr>
    <td><?= $user['id'] ?></td>
    <td><?= htmlspecialchars($user['email']) ?></td>
    <td><?= $user['confirmed'] ? "Yes" : "No" ?></td>
    <td>
        <?php // Prevent admin deletion
        if (!$user['is_admin']): ?>
            <a href="users.php?delete=<?= $user['id'] ?>"
               onclick="return confirm('Delete this user?');">Delete</a>
        <?php else: ?>
            (admin)
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
</table>

<?php include "../includes/footer.php"; ?>
