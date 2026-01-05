<?php
// webhooks/zeno_webhook.php

require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../config/ZenoConfig.php';

/**
 * Zeno Pay Webhook Handler
 * 
 * This endpoint receives payment status updates from Zeno Africa
 * URL: https://yourdomain.com/webhooks/zeno_webhook.php
 */

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

try {
    // Get raw POST data
    $rawData = file_get_contents('php://input');
    
    // Log webhook data (for debugging)
    error_log("Zeno Webhook Received: " . $rawData);
    
    // Parse JSON payload
    $data = json_decode($rawData, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON: " . json_last_error_msg());
    }
    
    // Extract webhook data
    $status = $data['status'] ?? null;
    $phoneNumber = $data['buyer_phone'] ?? null;
    $amount = isset($data['amount']) ? (int)$data['amount'] : null;
    $zenoOrderId = $data['order_id'] ?? null;
    $transactionId = $data['transaction_id'] ?? null;
    
    if (!$status || !$phoneNumber || !$amount) {
        throw new Exception("Missing required fields: status, buyer_phone, or amount");
    }
    
    // Find order by zeno_order_id
    if ($zenoOrderId) {
        $stmt = $conn->prepare("SELECT order_id, user_id, grand_total, payment_status FROM orders WHERE zeno_order_id = ?");
        $stmt->bind_param("s", $zenoOrderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
        
        if ($order) {
            $orderId = $order['order_id'];
            $userId = $order['user_id'];
            $orderAmount = (int)$order['grand_total'];
            
            // Verify amount matches
            if ($amount == $orderAmount) {
                // Update order payment status based on webhook status
                $paymentStatus = 'pending';
                $orderStatus = $order['status'] ?? 'pending';
                
                if ($status === 'success') {
                    $paymentStatus = 'paid';
                    // Don't auto-change order status, let admin handle it
                } elseif ($status === 'failed') {
                    $paymentStatus = 'failed';
                } elseif ($status === 'cancelled') {
                    $paymentStatus = 'cancelled';
                }
                
                // Update order
                $stmt = $conn->prepare("UPDATE orders SET payment_status = ?, zeno_transaction_id = ?, updated_at = NOW() WHERE order_id = ?");
                $stmt->bind_param("ssi", $paymentStatus, $transactionId, $orderId);
                $stmt->execute();
                $stmt->close();
                
                // Create notification for admin
                $notificationTitle = "Payment Update - Order #{$orderId}";
                $notificationMessage = "Payment status updated to: {$paymentStatus} for Order #{$orderId}";
                
                $stmt = $conn->prepare("INSERT INTO notifications (type, title, message, order_id, is_read) VALUES ('payment', ?, ?, ?, 0)");
                $stmt->bind_param("ssi", $notificationTitle, $notificationMessage, $orderId);
                $stmt->execute();
                $stmt->close();
                
                // If payment successful, also create notification for user
                if ($status === 'success' && $userId) {
                    $userNotificationTitle = "Payment Successful";
                    $userNotificationMessage = "Your payment for Order #{$orderId} has been received successfully.";
                    
                    $stmt = $conn->prepare("INSERT INTO notifications (type, title, message, order_id, user_id, is_read) VALUES ('payment', ?, ?, ?, ?, 0)");
                    $stmt->bind_param("ssii", $userNotificationTitle, $userNotificationMessage, $orderId, $userId);
                    $stmt->execute();
                    $stmt->close();
                }
                
                error_log("Order #{$orderId} payment status updated to: {$paymentStatus}");
            } else {
                error_log("Amount mismatch for order #{$orderId}: Expected {$orderAmount}, received {$amount}");
            }
        } else {
            error_log("Order not found for zeno_order_id: {$zenoOrderId}");
        }
    }
    
    // Always return 200 OK to acknowledge receipt
    http_response_code(200);
    echo 'OK';
    
} catch (Exception $e) {
    error_log("Webhook error: " . $e->getMessage());
    http_response_code(400);
    echo 'Error: ' . $e->getMessage();
}

