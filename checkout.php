<?php
session_start();
require_once 'db_connect.php';
require_once 'services/ZenoPaymentService.php';
require_once 'utils/PhoneFormatter.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

$error = '';
$success = '';

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transport_id = isset($_POST['transport_id']) ? (int)$_POST['transport_id'] : null;
    $subtotal = isset($_POST['subtotal']) ? (float)$_POST['subtotal'] : 0;
    $transport_cost = isset($_POST['transport_cost']) ? (float)$_POST['transport_cost'] : 0;
    $grand_total = isset($_POST['grand_total']) ? (float)$_POST['grand_total'] : 0;
    $delivery_address = isset($_POST['delivery_address']) ? trim($_POST['delivery_address']) : '';
    
    // Get user information
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT username, email, phone FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        $error = "User not found.";
    } elseif (!$transport_id) {
        $error = "Please select a delivery method.";
    } else {
        // Format phone number for Zeno Pay
        $buyerPhone = PhoneFormatter::formatForZeno($user['phone']);
        $buyerName = $user['username'];
        $buyerEmail = $user['email'] ?: ($buyerPhone . '@forexbot.com');
        
        // Prepare order items as JSON
        $order_items = json_encode($_SESSION['cart']);
        
        try {
            // Initiate payment with Zeno Pay
            $paymentResponse = ZenoPaymentService::initiatePayment(
                $buyerPhone,
                $buyerName,
                (int)$grand_total, // Amount in TZS (as integer)
                $buyerEmail
            );
            
            if (ZenoPaymentService::isPaymentSuccessful($paymentResponse)) {
                // Save order to database
                $zeno_order_id = $paymentResponse['order_id'] ?? null;
                $zeno_transaction_id = $paymentResponse['transaction_id'] ?? null;
                
                // Insert order into database
                $stmt = $conn->prepare("INSERT INTO orders (user_id, transport_id, transport_cost, total, grand_total, payment_status, zeno_order_id, zeno_transaction_id, buyer_phone, buyer_name, buyer_email, order_items, delivery_address, status) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, 'pending')");
                
                $stmt->bind_param("iidddsssssss", 
                    $user_id,
                    $transport_id,
                    $transport_cost,
                    $subtotal,
                    $grand_total,
                    $zeno_order_id,
                    $zeno_transaction_id,
                    $buyerPhone,
                    $buyerName,
                    $buyerEmail,
                    $order_items,
                    $delivery_address
                );
                
                if ($stmt->execute()) {
                    $order_id = $conn->insert_id;
                    
                    // Create notification for admin
                    $notification_title = "New Order #{$order_id}";
                    $notification_message = "New order from {$buyerName} for " . number_format($grand_total) . " TZS";
                    $stmt_notif = $conn->prepare("INSERT INTO notifications (type, title, message, order_id, is_read) VALUES ('order', ?, ?, ?, 0)");
                    $stmt_notif->bind_param("ssi", $notification_title, $notification_message, $order_id);
                    $stmt_notif->execute();
                    $stmt_notif->close();
                    
                    // Store order ID in session
                    $_SESSION['last_order_id'] = $order_id;
                    $_SESSION['zeno_order_id'] = $zeno_order_id;
                    
                    // Clear cart
                    unset($_SESSION['cart']);
                    
                    // Redirect to success page
                    header("Location: order_success.php?order_id=" . $order_id);
                    exit();
                } else {
                    $error = "Failed to save order: " . $conn->error;
                }
                $stmt->close();
            } else {
                $error = "Payment initiation failed: " . ($paymentResponse['message'] ?? 'Unknown error');
            }
        } catch (Exception $e) {
            $error = "Payment error: " . $e->getMessage();
        }
    }
}

// Calculate totals from cart
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['qty'];
}

// Get transport modules
$transport_query = "SELECT * FROM transport_modules WHERE status = 'active' ORDER BY price ASC";
$transport_result = $conn->query($transport_query);
$transport_modules = [];
if ($transport_result && $transport_result->num_rows > 0) {
    while ($row = $transport_result->fetch_assoc()) {
        $transport_modules[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Checkout - Bertran Foods</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f8f8f8;
            padding: 20px;
        }
        .container {
            max-width: 800px;
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
        .error {
            background: #ffe6e6;
            color: #d32f2f;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .success {
            background: #e6ffe6;
            color: #2e7d32;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        .transport-option {
            margin: 10px 0;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .transport-option:hover {
            border-color: #ff6347;
            background: #fff5f5;
        }
        .transport-option input[type="radio"] {
            width: auto;
            margin-right: 10px;
        }
        .summary {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .summary-total {
            display: flex;
            justify-content: space-between;
            padding-top: 15px;
            border-top: 2px solid #ddd;
            font-size: 20px;
            font-weight: bold;
            color: #2e7d32;
        }
        button {
            width: 100%;
            padding: 15px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.3s;
        }
        button:hover {
            background: #218838;
        }
        .cart-items {
            margin-bottom: 30px;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Checkout</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="checkout.php" id="checkoutForm">
            <div class="cart-items">
                <h3>Order Items</h3>
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="cart-item">
                        <span><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['qty']; ?></span>
                        <span><?php echo number_format($item['price'] * $item['qty']); ?> Tsh</span>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="form-group">
                <label>Delivery Address</label>
                <textarea name="delivery_address" placeholder="Enter your delivery address" required></textarea>
            </div>
            
            <div class="form-group">
                <label>Select Delivery Method</label>
                <?php foreach ($transport_modules as $transport): ?>
                    <div class="transport-option" onclick="selectTransport(<?php echo $transport['transport_id']; ?>, <?php echo $transport['price']; ?>)">
                        <input type="radio" name="transport_id" value="<?php echo $transport['transport_id']; ?>" 
                               id="transport_<?php echo $transport['transport_id']; ?>" required>
                        <label for="transport_<?php echo $transport['transport_id']; ?>" style="display:inline; cursor:pointer;">
                            <strong><?php echo htmlspecialchars($transport['name']); ?></strong>
                            <?php if ($transport['description']): ?>
                                <br><small style="color:#666;"><?php echo htmlspecialchars($transport['description']); ?></small>
                            <?php endif; ?>
                            <span style="float:right; color:green; font-weight:bold;">
                                <?php echo number_format($transport['price']); ?> Tsh
                            </span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span><?php echo number_format($subtotal); ?> Tsh</span>
                </div>
                <div class="summary-row">
                    <span>Delivery:</span>
                    <span id="deliveryCost">0 Tsh</span>
                </div>
                <div class="summary-total">
                    <span>Total:</span>
                    <span id="grandTotal"><?php echo number_format($subtotal); ?> Tsh</span>
                </div>
            </div>
            
            <input type="hidden" name="subtotal" value="<?php echo $subtotal; ?>">
            <input type="hidden" name="transport_cost" id="transport_cost" value="0">
            <input type="hidden" name="grand_total" id="grand_total_input" value="<?php echo $subtotal; ?>">
            
            <button type="submit">Pay with Zeno Pay</button>
        </form>
        
        <div style="margin-top: 20px; text-align: center;">
            <a href="cart.php" style="color: #ff6347; text-decoration: none;">← Back to Cart</a>
        </div>
    </div>
    
    <script>
    function selectTransport(transportId, transportPrice) {
        document.getElementById('transport_' + transportId).checked = true;
        document.getElementById('transport_cost').value = transportPrice;
        document.getElementById('deliveryCost').textContent = transportPrice.toLocaleString() + ' Tsh';
        
        var subtotal = <?php echo $subtotal; ?>;
        var total = subtotal + transportPrice;
        document.getElementById('grandTotal').textContent = total.toLocaleString() + ' Tsh';
        document.getElementById('grand_total_input').value = total;
    }
    </script>
</body>
</html>

