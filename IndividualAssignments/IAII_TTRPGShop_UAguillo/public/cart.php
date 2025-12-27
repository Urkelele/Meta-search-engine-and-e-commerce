<?php
include "../includes/header.php";
include "../includes/database.php";
include "../includes/utils.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Expiration time on seconds (10 minutes)
$expiration_time = 10 * 60;

// Cart session initialization
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Clean up expired items
foreach ($_SESSION['cart'] as $id => $item) {
    if (time() - $item['timestamp'] > $expiration_time) {

        // Return stock to DB
        $stmt = $conn->prepare("UPDATE items SET stock = stock + ? WHERE id = ?");
        $stmt->bind_param("ii", $item['quantity'], $id);
        $stmt->execute();

        // Remove from cart
        unset($_SESSION['cart'][$id]);
    }
}

// Add item to cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['item_id'])) {

    $item_id = intval($_POST['item_id']);

    // Check stock
    $stmt = $conn->prepare("SELECT stock FROM items WHERE id=?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $item = $res->fetch_assoc();

    if ($item['stock'] > 0) {

        // Reduce stock in DB
        $stmt = $conn->prepare("UPDATE items SET stock = stock - 1 WHERE id=?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();

        // Add to cart
        if (isset($_SESSION['cart'][$item_id])) {
            $_SESSION['cart'][$item_id]['quantity']++;
        } else {
            $_SESSION['cart'][$item_id] = [
                "quantity" => 1,
                "timestamp" => time()
            ];
        }

        // Prevent form resubmission
        clear_post();

        echo "<p style='color:green;'>Item added to cart!</p>";
    } else {
        echo "<p style='color:red;'>Out of stock!</p>";
    }
}

// Remove item from cart
if (isset($_GET['remove'])) {

    $remove_id = intval($_GET['remove']);

    if (isset($_SESSION['cart'][$remove_id])) {

        // Return stock to DB
        $qty = $_SESSION['cart'][$remove_id]['quantity'];
        $stmt = $conn->prepare("UPDATE items SET stock = stock + ? WHERE id=?");
        $stmt->bind_param("ii", $qty, $remove_id);
        $stmt->execute();

        unset($_SESSION['cart'][$remove_id]);
    }

    header("Location: cart.php");
    exit;
}

// Add quantity
if (isset($_GET['increase'])) {
    $id = intval($_GET['increase']);
    
    // Check stock
    $stmt = $conn->prepare("SELECT stock FROM items WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $item = $res->fetch_assoc();

    if ($item && $item['stock'] > 0) {
        // Reduce stock DB
        $stmt = $conn->prepare("UPDATE items SET stock = stock - 1 WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Increase session
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity']++;
        }
    }
    
    header("Location: cart.php");
    exit;
}

// Decrease quantity
if (isset($_GET['decrease'])) {
    $id = intval($_GET['decrease']);

    if (isset($_SESSION['cart'][$id])) {
        // Increase stock DB
        $stmt = $conn->prepare("UPDATE items SET stock = stock + 1 WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Decrease session
        $_SESSION['cart'][$id]['quantity']--;

        // Remove if quantity is 0
        if ($_SESSION['cart'][$id]['quantity'] <= 0) {
            unset($_SESSION['cart'][$id]);
        }
    }

    header("Location: cart.php");
    exit;
}

echo "<a href='index.php'>Return to Shop</a>";
?>
<h2>Your Cart</h2>

<?php if (empty($_SESSION['cart'])): ?>
    <p>Your cart is empty.</p>

<?php else: ?>

<table border="1" cellpadding="8">
<tr>
    <th>Item</th>
    <th>Price</th>
    <th>Quantity</th>
    <th>Total</th>
    <th>Action</th>
</tr>

<?php
$total_price = 0;

foreach ($_SESSION['cart'] as $id => $data):

    // Fetch product details
    $stmt = $conn->prepare("SELECT name, price FROM items WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    $subtotal = $product['price'] * $data['quantity'];
    $total_price += $subtotal;
?>
<tr>
    <td><?= htmlspecialchars($product['name']) ?></td>
    <td><?= $product['price'] ?>€</td>
    <td>
        <a href="cart.php?decrease=<?= $id ?>" style="text-decoration:none;">[-]</a>
        <?= $data['quantity'] ?>
        <a href="cart.php?increase=<?= $id ?>" style="text-decoration:none;">[+]</a>
    </td>
    <td><?= $subtotal ?>€</td>
    <td><a href="cart.php?remove=<?= $id ?>">Remove</a></td>
</tr>
<?php endforeach; ?>

</table>

<h3>Total: <?= $total_price ?>€</h3>

<br>

<!-- Payment button -->
<form action="payment.php" method="post">
    <button type="submit">Proceed to Payment</button>
</form>

<?php endif; ?>

<?php include "../includes/footer.php"; ?>
