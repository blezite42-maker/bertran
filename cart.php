<?php
session_start();
require_once 'db_connect.php';

// Add to cart
if (isset($_POST['add_to_cart'])) {
    $item = [
        'name' => $_POST['name'],
        'price' => $_POST['price'],
        'image' => $_POST['image'],
        'qty' => 1
    ];

    $found = false;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as &$cart_item) {
            if ($cart_item['name'] === $item['name']) {
                $cart_item['qty']++;
                $found = true;
                break;
            }
        }
    }
    if (!$found) {
        $_SESSION['cart'][] = $item;
    }
}

// Remove item
if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
}

// Fetch transport modules
$transport_query = "SELECT * FROM transport_modules WHERE status = 'active' ORDER BY price ASC";
$transport_result = $conn->query($transport_query);
$transport_modules = [];
if ($transport_result && $transport_result->num_rows > 0) {
    while ($row = $transport_result->fetch_assoc()) {
        $transport_modules[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your Cart</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #5c1164ff; padding: 20px; }
        h2 { color: #274355ff; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        th, td { padding: 12px; text-align: center; border-bottom: 1px solid #1a2027ff; }
        th { background: #681f21ff; color: white; }
        img { width: 80px; height: 60px; border-radius: 5px; object-fit: cover; transition: transform 0.3s ease; }
        tr:hover img { transform: scale(1.05); }
        .remove { color: red; text-decoration: none; font-weight: bold; }
        .total { font-weight: bold; color: green; }
        a.continue { display: inline-block; margin-top: 20px; text-decoration: none; padding: 10px 15px; background: #ff6347; color: white; border-radius: 6px; transition: background 0.3s ease; }
        a.continue:hover { background: #893185ff; }
    </style>
</head>
<body>

<h2>Your Cart</h2>

<table>
    <tr>
        <th>Image</th>
        <th>Food</th>
        <th>Price (Tsh)</th>
        <th>Qty</th>
        <th>Total (Tsh)</th>
        <th>Remove</th>
    </tr>
    <?php 
    $grand_total = 0;
    if (!empty($_SESSION['cart'])):
        foreach ($_SESSION['cart'] as $index => $item):
            $total = $item['price'] * $item['qty'];
            $grand_total += $total;
    ?>
    <tr>
        <td><img src="<?php echo htmlspecialchars($item['image']); ?>" alt=""></td>
        <td><?php echo htmlspecialchars($item['name']); ?></td>
        <td><?php echo number_format($item['price']); ?> Tsh</td>
        <td><?php echo $item['qty']; ?></td>
        <td class="total"><?php echo number_format($total); ?> Tsh</td>
        <td><a class="remove" href="cart.php?remove=<?php echo $index; ?>">✖</a></td>
    </tr>
    <?php endforeach; ?>
    <tr>
        <th colspan="4">Grand Total</th>
        <th colspan="2" class="total"><?php echo number_format($grand_total); ?> Tsh</th>
    </tr>
    <?php else: ?>
    <tr><td colspan="6">Your cart is empty.</td></tr>
    <?php endif; ?>
</table>

<a class="continue" href="index.php">← Continue Shopping</a>

<?php if (!empty($_SESSION['cart'])): ?>
<div style="margin-top:30px; background:white; padding:20px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1);">
    <div style="display:flex; justify-content:space-between; padding:15px; background:#f0f0f0; border-radius:5px; margin-bottom:20px;">
        <div>
            <strong>Subtotal:</strong>
            <span><?php echo number_format($grand_total); ?> Tsh</span>
        </div>
    </div>
    
    <div style="margin-top:20px;">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" style="text-decoration:none; display:inline-block; width:100%;">
                <button type="button" style="width:100%; background:#28a745;color:white;padding:12px 20px;border:none;border-radius:5px;cursor:pointer;font-size:16px;font-weight:bold;">Login to Checkout</button>
            </a>
        <?php else: ?>
            <a href="checkout.php" style="text-decoration:none; display:inline-block; width:100%;">
                <button type="button" style="width:100%; background:#28a745;color:white;padding:12px 20px;border:none;border-radius:5px;cursor:pointer;font-size:16px;font-weight:bold;">Proceed to Checkout</button>
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

</body>
</html>


