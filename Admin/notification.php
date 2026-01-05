<?php
session_start();
require_once __DIR__ . '/../db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle mark as read
if (isset($_GET['mark_read'])) {
    $notif_id = (int)$_GET['mark_read'];
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ?");
    $stmt->bind_param("i", $notif_id);
    $stmt->execute();
    $stmt->close();
    header("Location: notification.php");
    exit();
}

// Handle mark all as read
if (isset($_GET['mark_all_read'])) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
    $stmt->execute();
    $stmt->close();
    header("Location: notification.php");
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $notif_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM notifications WHERE notification_id = ?");
    $stmt->bind_param("i", $notif_id);
    $stmt->execute();
    $stmt->close();
    header("Location: notification.php");
    exit();
}

// Fetch all notifications
$sql = "
    SELECT 
        n.*,
        u.username AS user_name,
        o.order_id AS order_number
    FROM notifications AS n
    LEFT JOIN users AS u ON n.user_id = u.user_id
    LEFT JOIN orders AS o ON n.order_id = o.order_id
    ORDER BY n.created_at DESC
";

$result = $conn->query($sql);

// Count unread notifications
$unread_count = $conn->query("SELECT COUNT(*) as count FROM notifications WHERE is_read = 0")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications - Admin</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #f5f7fa; 
            margin: 0; 
            padding: 0; 
        }
        .container { 
            width: 95%; 
            margin: 20px auto; 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 6px rgba(0,0,0,0.1); 
        }
        h1 { 
            text-align: center; 
            color: #2c3e50; 
            margin-bottom: 20px;
        }
        .nav-links {
            margin-bottom: 20px;
        }
        .nav-links a {
            color: #1abc9c;
            text-decoration: none;
            margin-right: 15px;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .badge {
            display: inline-block;
            background: #ff6347;
            color: white;
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 14px;
            margin-left: 10px;
        }
        .btn {
            padding: 8px 16px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        .btn:hover {
            background: #218838;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .notification-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #fff;
            transition: all 0.3s;
        }
        .notification-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .notification-item.unread {
            background: #e7f3ff;
            border-left: 4px solid #1abc9c;
        }
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 10px;
        }
        .notification-title {
            font-weight: bold;
            font-size: 16px;
            color: #2c3e50;
            margin: 0;
        }
        .notification-meta {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .notification-message {
            color: #333;
            margin: 10px 0;
            line-height: 1.6;
        }
        .notification-actions {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }
        .notification-actions a {
            font-size: 12px;
            padding: 5px 10px;
        }
        .type-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin-right: 10px;
        }
        .type-order {
            background: #cfe2ff;
            color: #084298;
        }
        .type-payment {
            background: #d1e7dd;
            color: #0f5132;
        }
        .no-notifications {
            text-align: center;
            padding: 40px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            <a href="index.php">← Dashboard</a>
            <a href="view_orders.php">View Orders</a>
            <a href="manage_menu.php">Manage Menu</a>
            <a href="logout.php">Logout</a>
        </div>
        
        <h1>Notifications</h1>
        
        <div class="header-actions">
            <div>
                <strong>Total Notifications:</strong> <?php echo $result->num_rows; ?>
                <?php if ($unread_count > 0): ?>
                    <span class="badge"><?php echo $unread_count; ?> Unread</span>
                <?php endif; ?>
            </div>
            <?php if ($unread_count > 0): ?>
                <a href="?mark_all_read=1" class="btn">Mark All as Read</a>
            <?php endif; ?>
        </div>
        
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="notification-item <?php echo $row['is_read'] ? '' : 'unread'; ?>">
                    <div class="notification-header">
                        <div>
                            <span class="type-badge type-<?php echo strtolower($row['type']); ?>">
                                <?php echo htmlspecialchars($row['type']); ?>
                            </span>
                            <h3 class="notification-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                            <div class="notification-meta">
                                <?php if ($row['user_name']): ?>
                                    From: <?php echo htmlspecialchars($row['user_name']); ?> | 
                                <?php endif; ?>
                                <?php if ($row['order_number']): ?>
                                    Order #<?php echo $row['order_number']; ?> | 
                                <?php endif; ?>
                                <?php echo date('F j, Y g:i A', strtotime($row['created_at'])); ?>
                            </div>
                        </div>
                        <?php if (!$row['is_read']): ?>
                            <span style="background: #ff6347; color: white; padding: 3px 8px; border-radius: 50%; font-size: 10px;">NEW</span>
                        <?php endif; ?>
                    </div>
                    <div class="notification-message">
                        <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                    </div>
                    <div class="notification-actions">
                        <?php if (!$row['is_read']): ?>
                            <a href="?mark_read=<?php echo $row['notification_id']; ?>" class="btn">Mark as Read</a>
                        <?php endif; ?>
                        <?php if ($row['order_id']): ?>
                            <a href="view_orders.php#order_<?php echo $row['order_id']; ?>" class="btn" style="background: #3498db;">View Order</a>
                        <?php endif; ?>
                        <a href="?delete=<?php echo $row['notification_id']; ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this notification?');">Delete</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-notifications">
                <p>No notifications found.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
