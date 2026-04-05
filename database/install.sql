-- ========================================
-- VINA CASCARA - FULL DATABASE INSTALLER
-- For InfinityFree / Shared Hosting
-- ========================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- NOTE: Do NOT use CREATE DATABASE or USE on InfinityFree. 
-- The database is already created for you in the control panel.

-- 1. Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NULL,
  `avatar` VARCHAR(500) NULL,
  `role` ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  `google_id` VARCHAR(100) NULL UNIQUE,
  `phone` VARCHAR(20) NULL,
  `address` TEXT NULL,
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Product categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Products table (including specification)
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED NULL,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `short_description` TEXT NULL,
  `description` LONGTEXT NULL,
  `price` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `specification` VARCHAR(50) NULL,
  `compare_price` DECIMAL(12,2) NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `sku` VARCHAR(100) NULL UNIQUE,
  `weight` DECIMAL(8,2) NULL,
  `featured` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active','inactive','draft') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Product images
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `image_url` VARCHAR(500) NOT NULL,
  `alt_text` VARCHAR(255) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Cart
CREATE TABLE IF NOT EXISTS `cart` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `session_id` VARCHAR(255) NULL,
  `user_id` INT UNSIGNED NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_code` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` INT UNSIGNED NULL,
  `guest_name` VARCHAR(150) NULL,
  `guest_email` VARCHAR(255) NULL,
  `guest_phone` VARCHAR(20) NULL,
  `shipping_name` VARCHAR(150) NOT NULL,
  `shipping_phone` VARCHAR(20) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `shipping_province` VARCHAR(100) NULL,
  `shipping_district` VARCHAR(100) NULL,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `shipping_fee` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `discount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `payment_method` ENUM('cod','vnpay') NOT NULL DEFAULT 'cod',
  `payment_status` ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `order_status` ENUM('pending','confirmed','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `notes` TEXT NULL,
  `vnpay_txn_ref` VARCHAR(100) NULL,
  `vnpay_transaction_no` VARCHAR(100) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Order items
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `product_image` VARCHAR(500) NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `subtotal` DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Reviews
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `order_id` INT UNSIGNED NULL,
  `rating` TINYINT NOT NULL DEFAULT 5,
  `title` VARCHAR(255) NULL,
  `comment` TEXT NULL,
  `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- DATA SEEDING
-- ========================================

-- Admin (Admin@123)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `email_verified`) VALUES
('Admin Vina Cascara', 'admin@vinacascara.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- Category
INSERT INTO `categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Trà Cascara', 'tra-cascara', 'Bộ sưu tập trà từ vỏ trái cà phê Arabica Việt Nam');

-- Products
INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `short_description`, `description`, `price`, `specification`, `stock`, `featured`, `status`) VALUES
(1, 1, 'Trà Cascara Nguyên Chất', 'tra-cascara-nguyen-chat', 
'100% từ vỏ trái cà phê', 
'<ul><li>100% vỏ trái cà phê Arabica</li><li>Không chất bảo quản</li><li>Phơi thủ công trong nhà kính</li><li>Dễ pha chế</li><li>Thơm mùi trái cây khô</li></ul>', 
120000, '100G', 100, 1, 'active'),

(2, 1, 'Trà Cascara Dạng Túi Lọc', 'tra-cascara-dang-tui-loc', 
'Tiện lợi, dễ sử dụng', 
'<ul><li>100% vỏ trái cà phê Arabica</li><li>Không chất bảo quản</li><li>Phơi thủ công trong nhà kính</li><li>Dễ pha chế</li><li>Thơm mùi trái cây khô</li></ul>', 
110000, 'HỘP 10 TÚI', 150, 1, 'active'),

(3, 1, 'Trà Cascara Sấy & Rang', 'tra-cascara-say-rang', 
'Hương vị đậm đà', 
'<ul><li>100% vỏ trái cà phê Arabica</li><li>Không chất bảo quản</li><li>Sấy và rang thủ công</li><li>Dễ pha chế</li><li>Thơm mùi trái cây khô và cafe</li></ul>', 
120000, '100G', 80, 1, 'active');

-- Images
INSERT INTO `product_images` (`product_id`, `image_url`, `alt_text`, `sort_order`, `is_primary`) VALUES
(1, '/assets/images/product-1.png', 'Trà Cascara Nguyên Chất', 0, 1),
(2, '/assets/images/product-2.png', 'Trà Cascara Dạng Túi Lọc', 0, 1),
(3, '/assets/images/product-3.png', 'Trà Cascara Sấy và Rang', 0, 1);
