<?php
session_start();
include 'db_connect.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch stats
$total_orders = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'];
$total_users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$total_food = $conn->query("SELECT COUNT(*) AS total FROM food_items")->fetch_assoc()['total'];
$total_notifications = $conn->query("SELECT COUNT(*) AS total FROM notifications")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<style>
body {font-family: Arial; margin:0; display:flex; background:#f4f6f9;}
.sidebar {width:220px; background:#2c3e50; min-height:100vh; padding:20px 0; color:white;}
.sidebar h2 {text-align:center; margin-bottom:30px;}
.sidebar a {display:block; padding:12px 20px; color:white; text-decoration:none; transition:0.3s;}
.sidebar a:hover {background:#1abc9c;}
.content {flex:1; padding:20px;}
.card {background:white; padding:20px; border-radius:8px; margin-bottom:20px; box-shadow:0 2px 8px rgba(0,0,0,0.1);}
.card h3 {margin:0; color:#333;}
.card p {font-size:20px; font-weight:bold;}
table {width:100%; border-collapse: collapse; background:white; border-radius:8px; overflow:hidden;}
th, td {padding:12px; border-bottom:1px solid #ddd; text-align:center;}
th {background:#1abc9c; color:white;}
tr:hover {background:#f1f1f1;}
.btn {padding:8px 12px; border:none; border-radius:6px; cursor:pointer; color:white; text-decoration:none;}
.btn-edit {background:#3498db;}
.btn-delete {background:#e74c3c;}
</style>
</head>
<body>
<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="index.php">Dashboard</a>
    <a href="view_orders.php">View Orders</a>
    <a href="notifications.php">Notifications</a>
    <a href="statistics.php">Statistics</a>
    <a href="manage_menu.php">Manage Menu</a>
    <a href="logout.php" style="color:#e74c3c;">Logout</a>
</div>

<div class="content">
    <h1>Dashboard Overview</h1>

    <div class="card">
        <h3>Total Orders</h3>
        <p><?php echo $total_orders; ?></p>
    </div>
    <div class="card">
        <h3>Total Users</h3>
        <p><?php echo $total_users; ?></p>
    </div>
    <div class="card">
        <h3>Total Food Items</h3>
        <p><?php echo $total_food; ?></p>
    </div>
    <div class="card">
        <h3>Notifications</h3>
        <p><?php echo $total_notifications; ?></p>
    </div>
</div>
</body>
</html>
