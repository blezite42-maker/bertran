<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get order ID from query parameter
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id) {
    // Fetch order details
    $stmt = $conn->prepare("
        SELECT 
            o.*,
            t.name AS transport_name
        FROM orders AS o
        LEFT JOIN transport_modules AS t ON o.transport_id = t.transport_id
        WHERE o.order_id = ? AND o.user_id = ?
    ");
    $user_id = $_SESSION['user_id'];
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
} else {
    $order = null;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Successful - Bertran Foods</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f6faf7;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 500px;
        }
        .success-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        h1 {
            color: #2e7d32;
            margin-bottom: 10px;
        }
        p {
            color: #666;
            line-height: 1.6;
            margin: 10px 0;
        }
        .order-info {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #333;
        }
        .info-value {
            color: #666;
        }
        .total-amount {
            font-size: 24px;
            font-weight: bold;
            color: #2e7d32;
            margin-top: 15px;
        }
        .actions {
            margin-top: 30px;
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        a {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #2e7d32;
            color: white;
        }
        .btn-primary:hover {
            background: #256a29;
        }
        .btn-secondary {
            background: #ff6347;
            color: white;
        }
        .btn-secondary:hover {
            background: #e5533d;
        }
        .btn-outline {
            background: transparent;
            color: #666;
            border: 2px solid #ddd;
        }
        .btn-outline:hover {
            border-color: #999;
        }
        .payment-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }
        .payment-pending {
            background: #fff3cd;
            color: #856404;
        }
        .payment-paid {
            background: #d1e7dd;
            color: #0f5132;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="success-icon">✅</div>
        <h1>Order Placed Successfully!</h1>
        
        <?php if ($order): ?>
            <p>Thank you for your order! We've received your order and will process it shortly.</p>
            
            <div class="order-info">
                <div class="info-row">
                    <span class="info-label">Order ID:</span>
                    <span class="info-value">#<?php echo $order['order_id']; ?></span>
                </div>
                <?php if ($order['zeno_order_id']): ?>
                    <div class="info-row">
                        <span class="info-label">Payment Order ID:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['zeno_order_id']); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($order['transport_name']): ?>
                    <div class="info-row">
                        <span class="info-label">Delivery Method:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['transport_name']); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($order['delivery_address']): ?>
                    <div class="info-row">
                        <span class="info-label">Delivery Address:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['delivery_address']); ?></span>
                    </div>
                <?php endif; ?>
                <div class="info-row">
                    <span class="info-label">Order Status:</span>
                    <span class="info-value"><?php echo ucfirst($order['status']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment Status:</span>
                    <span class="info-value">
                        <span class="payment-status payment-<?php echo strtolower($order['payment_status']); ?>">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </span>
                </div>
                <div class="total-amount">
                    Total: <?php echo number_format($order['grand_total']); ?> Tsh
                </div>
            </div>
            
            <?php if ($order['payment_status'] === 'pending'): ?>
                <p style="color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px;">
                    ⚠️ Your payment is being processed. You will receive a notification once payment is confirmed.
                </p>
            <?php endif; ?>
        <?php else: ?>
            <p>Your order has been placed successfully!</p>
        <?php endif; ?>
        
        <div class="actions">
            <a href="my_orders.php" class="btn-primary">View My Orders</a>
            <a href="index.php" class="btn-secondary">Continue Shopping</a>
            <a href="dashboard.php" class="btn-outline">Dashboard</a>
        </div>
    </div>
</body>
</html>
