<h1>Your cart</h1>

<?php if (empty($items)): ?>
    <p>Your cart is empty.</p>
<?php else: ?>

<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>Product</th>
        <th>Price</th>
        <th>Shipping</th>
        <th>Qty</th>
        <th>Subtotal</th>
        <th>Remove</th>
    </tr>

    <?php $total = 0; ?>
    <?php foreach ($items as $item): ?>
        <?php $total += $item['subtotal']; ?>
        <tr>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td>€<?= number_format($item['price'], 2) ?></td>
            <td>€<?= number_format($item['shipping_price'], 2) ?></td>
            <td><?= (int)$item['quantity'] ?></td>
            <td>€<?= number_format($item['subtotal'], 2) ?></td>
            <td>
                <form method="post" action="?page=cart" style="display:inline">
                    <input type="hidden" name="remove_product_id" value="<?= $item['product_id'] ?>">
                    <button type="submit">Remove</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>

    <tr>
        <td colspan="4" align="right"><strong>Total</strong></td>
        <td><strong>€<?= number_format($total, 2) ?></strong></td>
    </tr>
</table>

<a href="?page=checkout">
    <button type="submit" style="margin-top:20px;padding:10px 20px;">Proceed to checkout</button>
</a>

<?php endif; ?>