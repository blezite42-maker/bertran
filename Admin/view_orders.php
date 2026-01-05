<?php
session_start();
include 'db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Clear and readable SQL query with joins for user and food names
$sql = "
    SELECT 
        o.order_id, 
        u.name AS user_name, 
        f.name AS food_name, 
        o.quantity, 
        o.total, 
        o.status
    FROM orders AS o
    JOIN users AS u ON o.user_id = u.user_id
    JOIN food_items AS f ON o.food_id = f.food_id
    ORDER BY o.order_id DESC
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
<title>View Orders</title>
<style>
body { font-family: Arial, sans-serif; background: #f5f7fa; margin:0; padding:0; }
.container { width: 90%; margin: 30px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
h1 { text-align: center; color: #2c3e50; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border-bottom: 1px solid #ddd; padding: 10px; text-align: center; }
th { background: #1abc9c; color: white; }
tr:hover { background: #f1f1f1; }
.status-pending { color: orange; font-weight: bold; }
.status-completed { color: green; font-weight: bold; }
</style>
</head>
<body>

<div class="container">
    <h1>View Orders</h1>
    <table>
        <tr>
            <th>Order ID</th>
            <th>User</th>
            <th>Food</th>
            <th>Quantity</th>
            <th>Total (Tsh)</th>
            <th>Status</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['order_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['food_name']); ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td><?php echo number_format($row['total']); ?></td>
                    <td>
                        <?php if ($row['status'] === 'pending'): ?>
                            <span class="status-pending">Pending</span>
                        <?php else: ?>
                            <span class="status-completed">Completed</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No orders found.</td></tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
