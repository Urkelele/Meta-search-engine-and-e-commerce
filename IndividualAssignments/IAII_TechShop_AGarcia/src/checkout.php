<?php

$pdo = DB::get();
require_once 'cart.php';

if (empty($_SESSION['user']['id'])) {
    header('Location: ?page=login');
    exit;
}

$userId = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (empty($_POST['card_number']) || empty($_POST['card_name'])) {
            throw new Exception('Payment information incomplete');
        }

        checkout($pdo, $userId);

        echo "<h2>Order completed successfully!</h2>";
        echo "<p>Your payment has been processed.</p>";
        echo "<p><a href='?page=products'>Continue shopping</a></p>";
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

function checkout(mysqli $db, int $userId): void
{
    $stmt = $db->prepare("
        SELECT cart_id FROM carts
        WHERE user_id = ? AND status = 'open'
        LIMIT 1
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $cart = $res->fetch_assoc();

    if (!$cart) {
        throw new Exception('No active cart');
    }

    $cartId = (int)$cart['cart_id'];

    $db->begin_transaction();

    try {
        // Calculate total
        $stmt = $db->prepare("
            SELECT SUM(ci.quantity * p.price) AS total
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.product_id
            WHERE ci.cart_id = ?
        ");
        $stmt->bind_param('i', $cartId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $total = (float)$row['total'];

        // Create order
        $stmt = $db->prepare("
            INSERT INTO orders (user_id, total_price, status, created_at)
            VALUES (?, ?, 'paid', NOW())
        ");
        $stmt->bind_param('id', $userId, $total);
        $stmt->execute();
        $orderId = $db->insert_id;

        // Copy items
        $stmt = $db->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            SELECT ?, ci.product_id, ci.quantity, p.price
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.product_id
            WHERE ci.cart_id = ?
        ");
        $stmt->bind_param('ii', $orderId, $cartId);
        $stmt->execute();

        // Close cart
        $stmt = $db->prepare("
            UPDATE carts
            SET status = 'completed', updated_at = NOW()
            WHERE cart_id = ?
        ");
        $stmt->bind_param('i', $cartId);
        $stmt->execute();

        $db->commit();

    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
}
?>

<h1>Payment</h1>

<?php if (!empty($error)): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post" style="max-width:400px">

    <div style="margin-bottom:10px">
        <label>Cardholder name</label><br>
        <input type="text" name="card_name" required style="width:100%;padding:8px">
    </div>

    <div style="margin-bottom:10px">
        <label>Card number</label><br>
        <input type="text" name="card_number" required maxlength="16"
               style="width:100%;padding:8px">
    </div>

    <div style="display:flex;gap:10px">
        <div>
            <label>Expiry</label><br>
            <input type="text" name="expiry" placeholder="MM/YY"
                   style="width:100px;padding:8px">
        </div>
        <div>
            <label>CVV</label><br>
            <input type="text" name="cvv" maxlength="4"
                   style="width:80px;padding:8px">
        </div>
    </div>

    <button type="submit" style="margin-top:20px;padding:10px 20px">
        Pay now
    </button>
</form>
<?php