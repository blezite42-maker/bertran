<?php
session_start();
require_once __DIR__ . '/../db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    
    // Validate status
    $allowed_statuses = ['pending', 'processing', 'completed', 'cancelled'];
    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE order_id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        
        if ($stmt->execute()) {
            // Create notification for user
            $order_stmt = $conn->prepare("SELECT user_id FROM orders WHERE order_id = ?");
            $order_stmt->bind_param("i", $order_id);
            $order_stmt->execute();
            $order_result = $order_stmt->get_result();
            $order_data = $order_result->fetch_assoc();
            $order_stmt->close();
            
            if ($order_data && $order_data['user_id']) {
                $notification_title = "Order Status Updated";
                $notification_message = "Your Order #{$order_id} status has been updated to: " . ucfirst($new_status);
                
                $notif_stmt = $conn->prepare("INSERT INTO notifications (type, title, message, order_id, user_id, is_read) VALUES ('order', ?, ?, ?, ?, 0)");
                $notif_stmt->bind_param("ssii", $notification_title, $notification_message, $order_id, $order_data['user_id']);
                $notif_stmt->execute();
                $notif_stmt->close();
            }
            
            $success_message = "Order status updated successfully!";
        } else {
            $error_message = "Failed to update order status: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error_message = "Invalid status selected.";
    }
}

// Fetch all orders with user and transport information
$sql = "
    SELECT 
        o.order_id, 
        u.username AS user_name,
        u.email AS user_email,
        u.phone AS user_phone,
        o.total,
        o.transport_cost,
        o.grand_total,
        o.status,
        o.payment_status,
        o.delivery_address,
        o.order_items,
        o.zeno_order_id,
        o.zeno_transaction_id,
        o.created_at,
        o.updated_at,
        t.name AS transport_name
    FROM orders AS o
    LEFT JOIN users AS u ON o.user_id = u.user_id
    LEFT JOIN transport_modules AS t ON o.transport_id = t.transport_id
    ORDER BY o.created_at DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Orders - Admin</title>
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
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .success {
            background: #d1e7dd;
            color: #0f5132;
        }
        .error {
            background: #f8d7da;
            color: #842029;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        th, td { 
            border-bottom: 1px solid #ddd; 
            padding: 12px; 
            text-align: left; 
        }
        th { 
            background: #1abc9c; 
            color: white; 
            font-weight: bold;
        }
        tr:hover { 
            background: #f1f1f1; 
        }
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
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
            max-width: 400px;
        }
        .order-items {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .status-form {
            display: inline-block;
        }
        .status-form select {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 5px;
        }
        .status-form button {
            padding: 5px 15px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
        }
        .status-form button:hover {
            background: #218838;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: #000;
        }
        .detail-row {
            margin: 10px 0;
            padding: 8px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .detail-label {
            font-weight: bold;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            <a href="index.php">← Dashboard</a>
            <a href="notification.php">Notifications</a>
            <a href="manage_menu.php">Manage Menu</a>
            <a href="logout.php">Logout</a>
        </div>
        
        <h1>View Orders</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <table>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Order Details</th>
                <th>Amount</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): 
                    $orderItems = json_decode($row['order_items'], true) ?? [];
                ?>
                    <tr>
                        <td>#<?php echo $row['order_id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['user_name']); ?></strong><br>
                            <small style="color:#666;"><?php echo htmlspecialchars($row['user_email']); ?></small><br>
                            <small style="color:#666;"><?php echo htmlspecialchars($row['user_phone']); ?></small>
                        </td>
                        <td class="order-details">
                            <?php if (!empty($orderItems)): ?>
                                <?php foreach (array_slice($orderItems, 0, 3) as $item): ?>
                                    <div><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['qty']; ?></div>
                                <?php endforeach; ?>
                                <?php if (count($orderItems) > 3): ?>
                                    <div class="order-items">+ <?php echo count($orderItems) - 3; ?> more items</div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ($row['transport_name']): ?>
                                <div class="order-items">Delivery: <?php echo htmlspecialchars($row['transport_name']); ?></div>
                            <?php endif; ?>
                            <?php if ($row['delivery_address']): ?>
                                <div class="order-items">Address: <?php echo htmlspecialchars(substr($row['delivery_address'], 0, 50)); ?>...</div>
                            <?php endif; ?>
                            <div class="order-items">Date: <?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></div>
                        </td>
                        <td>
                            <strong><?php echo number_format($row['grand_total']); ?> Tsh</strong><br>
                            <small style="color:#666;">
                                Subtotal: <?php echo number_format($row['total']); ?> Tsh<br>
                                <?php if ($row['transport_cost'] > 0): ?>
                                    Delivery: <?php echo number_format($row['transport_cost']); ?> Tsh
                                <?php endif; ?>
                            </small>
                        </td>
                        <td>
                            <span class="status-badge payment-<?php echo strtolower($row['payment_status']); ?>">
                                <?php echo htmlspecialchars($row['payment_status']); ?>
                            </span>
                            <?php if ($row['zeno_order_id']): ?>
                                <br><small style="color:#666;">ID: <?php echo htmlspecialchars($row['zeno_order_id']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" class="status-form" onsubmit="return confirm('Are you sure you want to update this order status?');">
                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                <select name="status" required>
                                    <option value="pending" <?php echo $row['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $row['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="completed" <?php echo $row['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $row['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_status">Update</button>
                            </form>
                            <button onclick="showOrderDetails(<?php echo htmlspecialchars(json_encode($row)); ?>)" 
                                    style="margin-top:5px; padding:5px 15px; background:#3498db; color:white; border:none; border-radius:5px; cursor:pointer; font-size:12px; width:100%;">
                                View Details
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align:center; padding:40px;">No orders found.</td></tr>
            <?php endif; ?>
        </table>
    </div>
    
    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Order Details</h2>
            <div id="orderDetails"></div>
        </div>
    </div>
    
    <script>
    function showOrderDetails(order) {
        const modal = document.getElementById('orderModal');
        const details = document.getElementById('orderDetails');
        
        let orderItems = [];
        try {
            orderItems = JSON.parse(order.order_items || '[]');
        } catch(e) {
            orderItems = [];
        }
        
        let itemsHtml = '<div class="detail-row"><span class="detail-label">Items:</span><ul>';
        orderItems.forEach(item => {
            itemsHtml += `<li>${item.name} x ${item.qty} - ${parseInt(item.price * item.qty).toLocaleString()} Tsh</li>`;
        });
        itemsHtml += '</ul></div>';
        
        details.innerHTML = `
            <div class="detail-row">
                <span class="detail-label">Order ID:</span> #${order.order_id}
            </div>
            <div class="detail-row">
                <span class="detail-label">Customer:</span> ${order.user_name} (${order.user_email})
            </div>
            <div class="detail-row">
                <span class="detail-label">Phone:</span> ${order.user_phone}
            </div>
            ${order.delivery_address ? `<div class="detail-row"><span class="detail-label">Delivery Address:</span> ${order.delivery_address}</div>` : ''}
            ${order.transport_name ? `<div class="detail-row"><span class="detail-label">Delivery Method:</span> ${order.transport_name}</div>` : ''}
            ${itemsHtml}
            <div class="detail-row">
                <span class="detail-label">Subtotal:</span> ${parseInt(order.total).toLocaleString()} Tsh
            </div>
            ${parseInt(order.transport_cost) > 0 ? `<div class="detail-row"><span class="detail-label">Delivery Cost:</span> ${parseInt(order.transport_cost).toLocaleString()} Tsh</div>` : ''}
            <div class="detail-row">
                <span class="detail-label">Grand Total:</span> <strong>${parseInt(order.grand_total).toLocaleString()} Tsh</strong>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status:</span> ${order.status}
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Status:</span> ${order.payment_status}
            </div>
            ${order.zeno_order_id ? `<div class="detail-row"><span class="detail-label">Zeno Order ID:</span> ${order.zeno_order_id}</div>` : ''}
            ${order.zeno_transaction_id ? `<div class="detail-row"><span class="detail-label">Transaction ID:</span> ${order.zeno_transaction_id}</div>` : ''}
            <div class="detail-row">
                <span class="detail-label">Created:</span> ${new Date(order.created_at).toLocaleString()}
            </div>
            <div class="detail-row">
                <span class="detail-label">Updated:</span> ${new Date(order.updated_at).toLocaleString()}
            </div>
        `;
        
        modal.style.display = 'block';
    }
    
    function closeModal() {
        document.getElementById('orderModal').style.display = 'none';
    }
    
    window.onclick = function(event) {
        const modal = document.getElementById('orderModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
    </script>
</body>
</html>
