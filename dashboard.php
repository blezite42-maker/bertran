<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get user statistics
$orders_stmt = $conn->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE user_id = ?");
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
$orders_data = $orders_result->fetch_assoc();
$total_orders = $orders_data['total_orders'] ?? 0;
$orders_stmt->close();

// Get pending orders count
$pending_stmt = $conn->prepare("SELECT COUNT(*) as pending_orders FROM orders WHERE user_id = ? AND status = 'pending'");
$pending_stmt->bind_param("i", $user_id);
$pending_stmt->execute();
$pending_result = $pending_stmt->get_result();
$pending_data = $pending_result->fetch_assoc();
$pending_orders = $pending_data['pending_orders'] ?? 0;
$pending_stmt->close();

// Get unread notifications count
$notif_stmt = $conn->prepare("SELECT COUNT(*) as unread_notifications FROM notifications WHERE user_id = ? AND is_read = 0");
$notif_stmt->bind_param("i", $user_id);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();
$notif_data = $notif_result->fetch_assoc();
$unread_notifications = $notif_data['unread_notifications'] ?? 0;
$notif_stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>User Dashboard - Bertran Foods</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f8f8f8;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: linear-gradient(45deg, #ff6347, #ff9966);
            padding: 20px 30px;
            border-radius: 12px;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
        }
        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #ff6347;
            margin: 10px 0;
        }
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .action-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        .action-card .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .action-card h3 {
            margin: 0;
            color: #ff6347;
        }
        .action-card p {
            margin: 10px 0 0 0;
            color: #666;
            font-size: 14px;
        }
        .badge {
            display: inline-block;
            background: #ff6347;
            color: white;
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 12px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="number"><?php echo $total_orders; ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Orders</h3>
                <div class="number"><?php echo $pending_orders; ?></div>
            </div>
            <div class="stat-card">
                <h3>Notifications</h3>
                <div class="number"><?php echo $unread_notifications; ?></div>
            </div>
        </div>
        
        <div class="actions-grid">
            <a href="index.php" class="action-card">
                <div class="icon">🛒</div>
                <h3>Shop Now</h3>
                <p>Browse our menu</p>
            </a>
            
            <a href="my_orders.php" class="action-card">
                <div class="icon">📦</div>
                <h3>My Orders</h3>
                <p>View your order history</p>
            </a>
            
            <a href="cart.php" class="action-card">
                <div class="icon">🛍️</div>
                <h3>Cart</h3>
                <p>View your cart</p>
            </a>
            
            <a href="logout.php" class="action-card">
                <div class="icon">🚪</div>
                <h3>Logout</h3>
                <p>Sign out</p>
            </a>
        </div>
    </div>
</body>
</html>
