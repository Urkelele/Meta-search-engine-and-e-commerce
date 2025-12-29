<?php
function getUserCart(mysqli $db, int $userId): int
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

    if ($cart) {
        return (int)$cart['cart_id'];
    }

    $stmt = $db->prepare("
        INSERT INTO carts (user_id, status, created_at, updated_at)
        VALUES (?, 'open', NOW(), NOW())
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();

    return (int)$db->insert_id;
}

function addToCart(mysqli $db, int $userId, int $productId, int $qty): void
{
    $cartId = getUserCart($db, $userId);

    $db->begin_transaction();

    try {
        $stmt = $db->prepare("
            SELECT available_stock
            FROM products
            WHERE product_id = ?
            FOR UPDATE
        ");
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $res = $stmt->get_result();
        $product = $res->fetch_assoc();

        if (!$product || $product['available_stock'] < $qty) {
            throw new Exception('Not enough stock');
        }

        // Reduce stock
        $stmt = $db->prepare("
            UPDATE products
            SET available_stock = available_stock - ?
            WHERE product_id = ?
        ");
        $stmt->bind_param('ii', $qty, $productId);
        $stmt->execute();

        // Insert / update cart item
        $stmt = $db->prepare("
            INSERT INTO cart_items (cart_id, product_id, quantity, added_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
        ");
        $stmt->bind_param('iii', $cartId, $productId, $qty);
        $stmt->execute();


        // Reserve
        $stmt = $db->prepare("
            UPDATE products SET available_stock = available_stock - ?,
                reserved_stock  = reserved_stock + ? WHERE product_id = ?
        ");
        $stmt->bind_param('iii', $qty, $qty, $productId);
        $stmt->execute();

        $db->commit();

    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
}

function removeFromCart(mysqli $db, int $userId, int $productId): void
{
    // Get open cart
    $stmt = $db->prepare("
        SELECT cart_id FROM carts
        WHERE user_id = ? AND status = 'open'
        LIMIT 1
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $cart = $res->fetch_assoc();

    if (!$cart) return;

    $cartId = (int)$cart['cart_id'];

    $db->begin_transaction();

    try {
        // Get quantity to restore
        $stmt = $db->prepare("
            SELECT quantity FROM cart_items
            WHERE cart_id = ? AND product_id = ?
        ");
        $stmt->bind_param('ii', $cartId, $productId);
        $stmt->execute();
        $res = $stmt->get_result();
        $item = $res->fetch_assoc();

        if (!$item) {
            $db->rollback();
            return;
        }

        $qty = (int)$item['quantity'];

        // Restore stock
        $stmt = $db->prepare("
            UPDATE products
            SET available_stock = available_stock + ?
            WHERE product_id = ?
        ");
        $stmt->bind_param('ii', $qty, $productId);
        $stmt->execute();

        // Remove item
        $stmt = $db->prepare("
            DELETE FROM cart_items
            WHERE cart_id = ? AND product_id = ?
        ");
        $stmt->bind_param('ii', $cartId, $productId);
        $stmt->execute();

        $db->commit();

    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
}


function expireCarts(mysqli $db): void
{
    $db->begin_transaction();

    $db->commit();
    $db->rollback();

    $db->commit();
}

$pdo = DB::get();
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user']['id'])) {
    header('Location: ?page=login');
    exit;
}

$userId = $_SESSION['user']['id'];

// Get open cart items
$stmt = $pdo->prepare("SELECT p.product_id, p.name, p.price, p.shipping_price, ci.quantity, (ci.quantity * (p.price + p.shipping_price)) AS subtotal FROM carts c
    JOIN cart_items ci ON c.cart_id = ci.cart_id
    JOIN products p ON ci.product_id = p.product_id
    WHERE c.user_id = ? AND c.status = 'open'
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
$items = $res->fetch_all(MYSQLI_ASSOC);

if($_GET['page'] == 'cart')
{
    require_once __DIR__ . '/../WebVisuals/cart_info.php';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_product_id'])) {
    removeFromCart($pdo, $userId, (int)$_POST['remove_product_id']);
    header('Location: ?page=cart');
    exit;
}

?>