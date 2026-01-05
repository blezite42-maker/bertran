<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user orders
$sql = "
    SELECT 
        o.order_id,
        o.total,
        o.transport_cost,
        o.grand_total,
        o.status,
        o.payment_status,
        o.delivery_address,
        o.order_items,
        o.created_at,
        t.name AS transport_name,
        o.zeno_order_id,
        o.zeno_transaction_id
    FROM orders AS o
    LEFT JOIN transport_modules AS t ON o.transport_id = t.transport_id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>My Orders - Bertran Foods</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f8f8f8;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h2 {
            color: #ff6347;
            margin-top: 0;
        }
        .order-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: #fafafa;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        .order-id {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .order-date {
            color: #666;
            font-size: 14px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-processing {
            background: #cfe2ff;
            color: #084298;
        }
        .status-completed {
            background: #d1e7dd;
            color: #0f5132;
        }
        .status-cancelled {
            background: #f8d7da;
            color: #842029;
        }
        .payment-paid {
            background: #d1e7dd;
            color: #0f5132;
        }
        .payment-pending {
            background: #fff3cd;
            color: #856404;
        }
        .payment-failed {
            background: #f8d7da;
            color: #842029;
        }
        .order-details {
            margin-top: 15px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }
        .detail-label {
            font-weight: bold;
            color: #666;
        }
        .detail-value {
            color: #333;
        }
        .order-items {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .total-row {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #ddd;
            font-size: 18px;
            font-weight: bold;
            color: #2e7d32;
        }
        .no-orders {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .nav-links {
            margin-bottom: 20px;
        }
        .nav-links a {
            color: #ff6347;
            text-decoration: none;
            margin-right: 15px;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            <a href="index.php">← Home</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
        
        <h2>My Orders</h2>
        
        <?php if (empty($orders)): ?>
            <div class="no-orders">
                <p>You haven't placed any orders yet.</p>
                <a href="index.php" style="color: #ff6347; text-decoration: none;">Start Shopping →</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): 
                $orderItems = json_decode($order['order_items'], true) ?? [];
            ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-id">Order #<?php echo $order['order_id']; ?></div>
                            <div class="order-date">Placed on <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></div>
                        </div>
                        <div>
                            <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </span>
                            <span class="status-badge payment-<?php echo strtolower($order['payment_status']); ?>" style="margin-left: 10px;">
                                Payment: <?php echo htmlspecialchars($order['payment_status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="order-details">
                        <?php if ($order['delivery_address']): ?>
                            <div class="detail-row">
                                <span class="detail-label">Delivery Address:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($order['delivery_address']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($order['transport_name']): ?>
                            <div class="detail-row">
                                <span class="detail-label">Delivery Method:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($order['transport_name']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($order['zeno_order_id']): ?>
                            <div class="detail-row">
                                <span class="detail-label">Payment Order ID:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($order['zeno_order_id']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($orderItems)): ?>
                        <div class="order-items">
                            <strong>Order Items:</strong>
                            <?php foreach ($orderItems as $item): ?>
                                <div class="order-item">
                                    <span><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['qty']; ?></span>
                                    <span><?php echo number_format($item['price'] * $item['qty']); ?> Tsh</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="total-row">
                        <div class="detail-row">
                            <span>Subtotal:</span>
                            <span><?php echo number_format($order['total']); ?> Tsh</span>
                        </div>
                        <?php if ($order['transport_cost'] > 0): ?>
                            <div class="detail-row">
                                <span>Delivery:</span>
                                <span><?php echo number_format($order['transport_cost']); ?> Tsh</span>
                            </div>
                        <?php endif; ?>
                        <div class="detail-row" style="font-size: 20px; font-weight: bold; color: #2e7d32;">
                            <span>Total:</span>
                            <span><?php echo number_format($order['grand_total']); ?> Tsh</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>

