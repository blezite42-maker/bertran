<?php
session_start();
include 'db_connect.php';
if (!isset($_SESSION['admin_id'])) header("Location: login.php");

// Example stats
$total_payments = $conn->query("SELECT SUM(amount) AS total FROM payments")->fetch_assoc()['total'];
$total_orders = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'];
?>
<h1>Statistics</h1>
<p>Total Orders: <?php echo $total_orders; ?></p>
<p>Total Payments: <?php echo number_format($total_payments); ?> Tsh</p>
