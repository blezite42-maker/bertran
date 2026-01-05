<?php
// services/ZenoPaymentService.php

require_once __DIR__ . '/../config/ZenoConfig.php';

class ZenoPaymentService {
    
    /**
     * Initiate a payment with Zeno Pay
     * 
     * @param string $buyerPhone Phone number with country code (e.g., "255712345678")
     * @param string $buyerName Buyer's full name
     * @param float|int $amount Payment amount
     * @param string|null $buyerEmail Optional email (defaults to phone@forexbot.com)
     * @return array Response data from Zeno API
     * @throws Exception If payment initiation fails
     */
    public static function initiatePayment(
        string $buyerPhone,
        string $buyerName,
        $amount,
        ?string $buyerEmail = null
    ): array {
        // Generate email if not provided
        if (!$buyerEmail) {
            $buyerEmail = $buyerPhone . '@forexbot.com';
        }
        
        // Prepare payload
        $payload = [
            'create_order' => '1',
            'buyer_email' => $buyerEmail,
            'buyer_name' => $buyerName,
            'buyer_phone' => $buyerPhone,
            'amount' => (string)$amount,
            'account_id' => ZenoConfig::ACCOUNT_ID,
            'api_key' => ZenoConfig::API_KEY,
            'secret_key' => ZenoConfig::SECRET_KEY,
            'currency' => ZenoConfig::CURRENCY,
            'payment_method' => ZenoConfig::PAYMENT_METHOD
        ];
        
        // Initialize cURL
        $ch = curl_init(ZenoConfig::API_URL);
        
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => ZenoConfig::REQUEST_TIMEOUT,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ]
        ]);
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($error) {
            throw new Exception("cURL Error: " . $error);
        }
        
        // Handle HTTP errors
        if ($httpCode !== 200) {
            throw new Exception("HTTP Error {$httpCode}: {$response}");
        }
        
        // Parse JSON response
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response: " . json_last_error_msg());
        }
        
        return $data;
    }
    
    /**
     * Check if payment was successful
     * 
     * @param array $response Response from initiatePayment()
     * @return bool
     */
    public static function isPaymentSuccessful(array $response): bool {
        return isset($response['status']) && $response['status'] === 'success';
    }
}

