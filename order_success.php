<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get order ID from session or query
$order_id = $_GET['order_id'] ?? ($_SESSION['last_order_id'] ?? null);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Successful</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f6faf7;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: #254e69ff;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 400px;
        }
        h1 {
            color: #2e7d32;
            margin-bottom: 10px;
        }
        p {
            color: #fff;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            background: #2e7d32;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
        }
        a:hover {
            background: #256a29;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🎉 Order Successful!</h1>
        <?php if ($order_id): ?>
            <p>Your order <strong>#<?php echo htmlspecialchars($order_id); ?></strong> has been placed successfully.</p>
        <?php else: ?>
            <p>Your order has been placed successfully.</p>
        <?php endif; ?>
        
        <!-- Back to Home -->
        <a href="index.php">Back to Home</a>
        
        <!-- Logout Button -->
        <a href="logout.php" style="background:#d32f2f; margin-left:10px;">Logout</a>
    </div>
</body>
</html>
