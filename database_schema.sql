-- Database Schema for Bertran Food Ordering System
-- This file contains the necessary table structures

-- Users table (if not exists)
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Food items table (if not exists)
CREATE TABLE IF NOT EXISTS `food_items` (
  `food_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`food_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Transport modules table
CREATE TABLE IF NOT EXISTS `transport_modules` (
  `transport_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`transport_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders table (enhanced)
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `food_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `total` decimal(10,2) NOT NULL,
  `transport_id` int(11) DEFAULT NULL,
  `transport_cost` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `payment_status` varchar(50) DEFAULT 'pending',
  `zeno_order_id` varchar(100) DEFAULT NULL,
  `zeno_transaction_id` varchar(100) DEFAULT NULL,
  `buyer_phone` varchar(20) DEFAULT NULL,
  `buyer_name` varchar(255) DEFAULT NULL,
  `buyer_email` varchar(255) DEFAULT NULL,
  `order_items` text DEFAULT NULL COMMENT 'JSON array of cart items',
  `delivery_address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`),
  KEY `food_id` (`food_id`),
  KEY `transport_id` (`transport_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`food_id`) REFERENCES `food_items` (`food_id`) ON DELETE SET NULL,
  FOREIGN KEY (`transport_id`) REFERENCES `transport_modules` (`transport_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notifications table (enhanced)
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) DEFAULT 'order',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admins table (if not exists)
CREATE TABLE IF NOT EXISTS `admins` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default transport modules
INSERT INTO `transport_modules` (`name`, `description`, `price`, `status`) VALUES
('Standard Delivery', 'Regular delivery within 2-3 hours', 5000.00, 'active'),
('Express Delivery', 'Fast delivery within 1 hour', 10000.00, 'active'),
('Pickup', 'Customer picks up from store', 0.00, 'active')
ON DUPLICATE KEY UPDATE `name`=`name`;

