<?php
// Admin protection
if (empty($_SESSION['user']) || $_SESSION['user']['is_admin'] != 1) {
    http_response_code(403);
    exit('Admin only');
}

$db = DB::get();

// Handle delete
if (isset($_GET['delete'])) {
    $userId = (int)$_GET['delete'];

    // Prevent deleting yourself
    if ($userId === (int)$_SESSION['user']['id']) {
        header('Location: ?page=admin_users');
        exit;
    }

    $stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();

    header('Location: ?page=admin_users');
    exit;
}

?>

<h1>Users</h1>

<table border="1">
<tr>
    <th>ID</th>
    <th>Email</th>
    <th>Name</th>
    <th>Admin</th>
    <th>Is verified</th>
    <th>Created at</th>
    <th>Action</th>
</tr>

<?php

$res = $db->query("SELECT user_id, email, name, is_verified, is_admin, created_at FROM users");
while ($u = $res->fetch_assoc()):
?>
<tr>
    <td><?= $u['user_id'] ?></td>
    <td><?= htmlspecialchars($u['email']) ?></td>
    <td><?= htmlspecialchars($u['name']) ?></td>
    <td><?= $u['is_verified'] ? 'Yes' : 'No' ?></td>
    <td><?= $u['is_admin'] ? 'Yes' : 'No' ?></td>
    <td><?= htmlspecialchars($u['created_at']) ?></td>
    <td>
        <?php if (!$u['is_admin']): ?>
            <a href="?page=admin_users&delete=<?= $u['user_id'] ?>"
               onclick="return confirm('Delete user?')">Delete</a>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
</table>
