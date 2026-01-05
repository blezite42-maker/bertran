<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
   <title>Dashboard</title>
</head>
<body>
   <h1>Welcome!</h1>


<?php
session_start();
include("db_connect.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Example: count payments
$stmt = $conn->prepare("SELECT COUNT(*) as total_payments FROM payments WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$payments = $result->fetch_assoc();
$total_payments = $payments['total_payments'];

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>User Dashboard</title>
</head>
<body>
<h2>Welcome, <?php echo htmlspecialchars($username); ?></h2>
<p>Total Payments Made: <?php echo $total_payments; ?></p>
<a href="payment.php">Make a Payment</a><br><br>
<a href="logout.php">Logout</a>
</body>
</html>
