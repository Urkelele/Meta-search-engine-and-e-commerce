<?php
$pdo = DB::get();
$id = (int)($_GET['id'] ?? 0);
if (!$id) { echo "<p>Invalid product</p>"; return; }

// Fetch product details
$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, a.name AS subcategory_name
                       FROM products p
                       LEFT JOIN categories c ON p.category_id = c.category_id
                       LEFT JOIN product_attributes s ON p.product_id = s.product_id
                       LEFT JOIN attributes a ON a.attribute_id = s.attribute_id
                       WHERE p.product_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();

$p = null;
$subcategories = [];

while ($row = $res->fetch_assoc()) {
    if($p == null)
    {
        $p = $row;
    }
    if(!empty($row['subcategory_name']))
    {
        $subcategories[] = $row['subcategory_name'];
    }
}
if ($res) { $res->free(); }
$stmt->close();
if (!$p) { echo "<p>Product not found</p>"; return; }

require_once 'cart.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qty'])) {

    if (empty($_SESSION['user']['id'])) {
        header('Location: ?page=login');
        exit;
    }

    $qty = max(1, (int)$_POST['qty']);
    $productId = (int)$p['product_id'];

    try {
        addToCart($pdo, $_SESSION['user']['id'], $productId, $qty);

        if (isset($_POST['buy_now'])) {
            header("Location: ?page=checkout");
        } else {
            header("Location: ?page=cart");
        }

        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

?>
<div style="display:flex;gap:20px;">
    <div style="width:320px">
        <?php
        $img = null;
        $mediaDir = __DIR__ . '/../media/productsImages';
        $mediaURL = explode("/index.php", $_SERVER['REQUEST_URI'])[0] . '/media/productsImages/';
        $baseName = 'Product' . $p['product_id'];
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        foreach ($extensions as $ext) {
            $filePath = $mediaDir . '/' . $baseName . '.' . $ext;
            if (file_exists($filePath)) {
                $img = $mediaURL . $baseName . '.' . $ext;
                break;
            }
        }

        if ($img): ?>
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="width:100%;border:1px solid #ddd;">
        <?php else: ?>
            <div style="width:100%;height:240px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;color:#999;border:1px solid #ddd">No image</div>
        <?php endif; ?>
    </div>

    <div>
        <h1 style="margin-top:0;"><?= htmlspecialchars($p['name'] ?? $p['title'] ?? '') ?></h1>
        <p style="color:#666;margin-top:4px;">Category: 
            <?= htmlspecialchars($p['category_name'] ?? '') ?> </p>
            <?php if (!empty($subcategories)): ?>
                <p>Subcategories:</p>
                <ul>
                    <?php foreach ($subcategories as $s): ?>
                        <li><?= htmlspecialchars($s) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <p style="margin-top:12px;"><?= nl2br(htmlspecialchars($p['description'])) ?></p>
        <p style="font-size:20px;font-weight:700;color:#28a745">€<?= number_format($p['price'] ?? $p['base_price'] ?? 0,2) ?></p>

        <p style="font-size:14px;color:#555;">
            Shipping: <strong>€<?= number_format($p['shipping_price'], 2) ?></strong>
        </p>

        <p style="font-size:18px;font-weight:700;">
            Total: €<?= number_format($p['price'] + $p['shipping_price'], 2) ?>
        </p>

        <p>Available stock: <strong><?= (int)$p['available_stock'] ?></strong></p>

        <?php if($error): ?><p style="color:red"><?= htmlspecialchars($error) ?></p><?php endif; ?>

        <form method="post" style="margin-top: 20px;">
          <div style="margin-bottom: 10px;">
            <label>Quantity:</label>
            <input type="number" name="qty" value="1" min="1" max="<?= $totalStock ?>" style="padding: 8px; width: 80px;">
          </div>
          <div style="display: flex; gap: 10px;">
            <button type="submit" name="add" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">Add to cart</button>
            <button type="submit" name="buy_now" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Buy now</button>
          </div>
        </form>
    </div>
</div>
