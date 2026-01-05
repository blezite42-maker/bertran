<?php
// utils/PhoneFormatter.php

class PhoneFormatter {
    /**
     * Format phone number for Zeno Pay
     * Removes spaces, dashes, and ensures country code format
     * 
     * @param string $phoneNumber Raw phone number
     * @return string Formatted phone number
     */
    public static function formatForZeno(string $phoneNumber): string {
        // Remove all non-digit characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // If starts with 0, replace with country code (255 for Tanzania)
        if (substr($cleaned, 0, 1) === '0') {
            $cleaned = '255' . substr($cleaned, 1);
        }
        
        // If doesn't start with country code, add it
        if (substr($cleaned, 0, 3) !== '255') {
            $cleaned = '255' . $cleaned;
        }
        
        return $cleaned;
    }
}

