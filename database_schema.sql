-- ============================================================================
-- Database Schema for Bertran Food Ordering System
-- Complete schema with all features: User Management, Orders, Payments, 
-- Notifications, Product Management, and Admin Panel
-- ============================================================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `bertran` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `bertran`;

-- ============================================================================
-- USERS TABLE
-- Stores user account information for customers
-- ============================================================================
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  KEY `idx_email` (`email`),
  KEY `idx_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- FOOD ITEMS TABLE
-- Stores product/menu items with pricing and images
-- ============================================================================
CREATE TABLE IF NOT EXISTS `food_items` (
  `food_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL COMMENT 'Legacy image field',
  `image_url` varchar(255) DEFAULT NULL COMMENT 'Path to uploaded image',
  `status` varchar(20) DEFAULT 'active' COMMENT 'active, pending, inactive',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`food_id`),
  KEY `idx_status` (`status`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TRANSPORT MODULES TABLE
-- Stores delivery/transport options with pricing
-- ============================================================================
CREATE TABLE IF NOT EXISTS `transport_modules` (
  `transport_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) DEFAULT 'active' COMMENT 'active, inactive',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`transport_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ORDERS TABLE
-- Stores all order information including payment details from Zeno Pay
-- ============================================================================
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `food_id` int(11) DEFAULT NULL COMMENT 'Legacy field - kept for backward compatibility',
  `quantity` int(11) NOT NULL DEFAULT 1 COMMENT 'Legacy field - kept for backward compatibility',
  `total` decimal(10,2) NOT NULL COMMENT 'Subtotal before delivery',
  `transport_id` int(11) DEFAULT NULL,
  `transport_cost` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL COMMENT 'Total including delivery',
  `status` varchar(50) DEFAULT 'pending' COMMENT 'pending, processing, completed, cancelled',
  `payment_status` varchar(50) DEFAULT 'pending' COMMENT 'pending, paid, failed, cancelled',
  `zeno_order_id` varchar(100) DEFAULT NULL COMMENT 'Order ID from Zeno Pay API',
  `zeno_transaction_id` varchar(100) DEFAULT NULL COMMENT 'Transaction ID from Zeno Pay',
  `buyer_phone` varchar(20) DEFAULT NULL COMMENT 'Phone number used for payment',
  `buyer_name` varchar(255) DEFAULT NULL COMMENT 'Buyer name from payment',
  `buyer_email` varchar(255) DEFAULT NULL COMMENT 'Buyer email from payment',
  `order_items` text DEFAULT NULL COMMENT 'JSON array of cart items: [{"name":"...","price":...,"qty":...}]',
  `delivery_address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_food_id` (`food_id`),
  KEY `idx_transport_id` (`transport_id`),
  KEY `idx_status` (`status`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_zeno_order_id` (`zeno_order_id`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`food_id`) REFERENCES `food_items` (`food_id`) ON DELETE SET NULL,
  FOREIGN KEY (`transport_id`) REFERENCES `transport_modules` (`transport_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- NOTIFICATIONS TABLE
-- Stores notifications for both users and admins
-- ============================================================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) DEFAULT 'order' COMMENT 'order, payment, system',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `order_id` int(11) DEFAULT NULL COMMENT 'Related order if applicable',
  `user_id` int(11) DEFAULT NULL COMMENT 'User ID if user-specific notification, NULL for admin notifications',
  `is_read` tinyint(1) DEFAULT 0 COMMENT '0 = unread, 1 = read',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_type` (`type`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ADMINS TABLE
-- Stores admin account information
-- ============================================================================
CREATE TABLE IF NOT EXISTS `admins` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- DEFAULT DATA INSERTION
-- ============================================================================

-- Insert default transport modules
INSERT INTO `transport_modules` (`name`, `description`, `price`, `status`) VALUES
('Standard Delivery', 'Regular delivery within 2-3 hours', 5000.00, 'active'),
('Express Delivery', 'Fast delivery within 1 hour', 10000.00, 'active'),
('Pickup', 'Customer picks up from store', 0.00, 'active')
ON DUPLICATE KEY UPDATE `name`=`name`;

-- ============================================================================
-- INDEXES SUMMARY
-- ============================================================================
-- Users: email, phone
-- Food Items: status, name
-- Transport Modules: status
-- Orders: user_id, food_id, transport_id, status, payment_status, zeno_order_id, created_at
-- Notifications: order_id, user_id, is_read, type, created_at
-- Admins: email

-- ============================================================================
-- NOTES
-- ============================================================================
-- 1. All tables use utf8mb4 charset for full Unicode support
-- 2. Foreign keys ensure referential integrity
-- 3. Indexes are added for frequently queried columns
-- 4. Orders table supports both legacy single-item orders and new multi-item cart orders
-- 5. Notifications can be user-specific or admin-wide (user_id = NULL)
-- 6. Payment integration fields (zeno_order_id, zeno_transaction_id) support Zeno Pay
-- 7. All timestamps use CURRENT_TIMESTAMP for automatic date tracking
