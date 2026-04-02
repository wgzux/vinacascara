-- ========================================
-- VINA CASCARA E-COMMERCE DATABASE SCHEMA
-- ========================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

CREATE DATABASE IF NOT EXISTS vinacascara CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vinacascara;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NULL COMMENT 'NULL for Google OAuth users',
  `avatar` VARCHAR(500) NULL,
  `role` ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  `google_id` VARCHAR(100) NULL UNIQUE,
  `phone` VARCHAR(20) NULL,
  `address` TEXT NULL,
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED NULL,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `short_description` TEXT NULL,
  `description` LONGTEXT NULL,
  `price` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `compare_price` DECIMAL(12,2) NULL COMMENT 'Original price for discount display',
  `stock` INT NOT NULL DEFAULT 0,
  `sku` VARCHAR(100) NULL UNIQUE,
  `weight` DECIMAL(8,2) NULL COMMENT 'In grams',
  `featured` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active','inactive','draft') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product images (for carousel)
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

-- Cart (supports both guest session and logged-in users)
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

-- Orders
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

-- Order items
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NULL,
  `product_name` VARCHAR(255) NOT NULL COMMENT 'Snapshot',
  `product_image` VARCHAR(500) NULL COMMENT 'Snapshot',
  `price` DECIMAL(12,2) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `subtotal` DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product reviews
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `order_id` INT UNSIGNED NULL,
  `rating` TINYINT NOT NULL DEFAULT 5 COMMENT '1-5 stars',
  `title` VARCHAR(255) NULL,
  `comment` TEXT NULL,
  `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Coupon/Discount codes
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `type` ENUM('percentage','fixed') NOT NULL DEFAULT 'fixed',
  `value` DECIMAL(10,2) NOT NULL,
  `min_order` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `max_uses` INT NULL,
  `used_count` INT NOT NULL DEFAULT 0,
  `valid_from` DATE NULL,
  `valid_until` DATE NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- SEED DATA
-- ========================================

-- Default admin user (password: Admin@123)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `email_verified`) VALUES
('Admin Vina Cascara', 'admin@vinacascara.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- Category
INSERT INTO `categories` (`name`, `slug`, `description`) VALUES
('Trà Cascara', 'tra-cascara', 'Bộ sưu tập trà từ vỏ trái cà phê Arabica Việt Nam');

-- Products
INSERT INTO `products` (`category_id`, `name`, `slug`, `short_description`, `description`, `price`, `stock`, `featured`, `status`) VALUES
(1, 'Trà Cascara Nguyên Chất', 'tra-cascara-nguyen-chat',
 '100% vỏ trái cà phê Arabica, phơi thủ công trong nhà kính, thơm mùi trái cây khô.',
 '<p>Trà Cascara Nguyên Chất là sản phẩm đặc biệt được làm từ 100% vỏ trái cà phê Arabica cao nguyên Việt Nam. Vỏ trái được thu hoạch thủ công, phơi cẩn thận trong nhà kính để giữ trọn hương vị tự nhiên.</p>
 <h4>Hương vị:</h4>
 <ul>
   <li>Thơm mùi trái cây khô đặc trưng</li>
   <li>Vị ngọt thanh, nhẹ nhàng</li>
   <li>Hậu vị dài, dễ chịu</li>
 </ul>
 <h4>Cách pha:</h4>
 <p>Dùng 10-15g trà cho 300-400ml nước nóng 85-90°C. Ngâm 5-7 phút. Có thể thêm đá hoặc uống nóng.</p>
 <h4>Thành phần:</h4>
 <p>100% vỏ trái cà phê Arabica Việt Nam. Không chất bảo quản, không phẩm màu.</p>',
 120000, 100, 1, 'active'),

(1, 'Trà Cascara Dạng Túi Lọc', 'tra-cascara-tui-loc',
 'Tiện lợi, dễ sử dụng. 100% vỏ trái cà phê Arabica, phơi thủ công trong nhà kính.',
 '<p>Trà Cascara Dạng Túi Lọc – lựa chọn hoàn hảo cho những ai muốn thưởng thức cascara tiện lợi mọi lúc mọi nơi. Mỗi túi lọc chứa đủ lượng trà chuẩn để pha một ly hoàn hảo.</p>
 <h4>Ưu điểm:</h4>
 <ul>
   <li>Tiện lợi, không cần dụng cụ lọc</li>
   <li>Hàm lượng chuẩn mỗi túi</li>
   <li>Dễ mang theo khi đi làm, du lịch</li>
 </ul>
 <h4>Cách pha:</h4>
 <p>Cho túi lọc vào ly, rót 300-400ml nước nóng 85-90°C. Ngâm 5-7 phút rồi bỏ túi ra.</p>
 <h4>Thành phần:</h4>
 <p>100% vỏ trái cà phê Arabica Việt Nam. Không chất bảo quản, không phẩm màu.</p>',
 110000, 150, 1, 'active'),

(1, 'Trà Cascara Sấy & Rang', 'tra-cascara-say-rang',
 'Hương vị đậm đà, sấy và rang thủ công, thơm mùi trái cây khô và cafe.',
 '<p>Trà Cascara Sấy & Rang là phiên bản đặc biệt với quy trình sấy và rang thủ công tạo nên hương vị đậm đà, phức hợp. Sự kết hợp giữa trái cây khô và cà phê tạo nên một trải nghiệm vị giác độc đáo.</p>
 <h4>Hương vị:</h4>
 <ul>
   <li>Thơm mùi trái cây khô và cà phê rang</li>
   <li>Vị đậm đà, mạnh mẽ hơn bản nguyên chất</li>
   <li>Màu sắc nâu đậm đẹp mắt</li>
 </ul>
 <h4>Cách pha:</h4>
 <p>Dùng 12-15g trà cho 300-400ml nước nóng 90-95°C. Ngâm 5-8 phút.</p>
 <h4>Thành phần:</h4>
 <p>100% vỏ trái cà phê Arabica Việt Nam sấy & rang thủ công. Không chất bảo quản, không phẩm màu.</p>',
 120000, 80, 1, 'active');

-- Product images (using original website images as placeholders)
INSERT INTO `product_images` (`product_id`, `image_url`, `alt_text`, `sort_order`, `is_primary`) VALUES
(1, 'https://vinacascara.lovable.app/assets/vina-cascara-1-CStrgfSG.png', 'Trà Cascara Nguyên Chất', 0, 1),
(1, 'https://vinacascara.lovable.app/assets/vina-cascara-2-B4YfnOpF.png', 'Trà Cascara Nguyên Chất 2', 1, 0),
(2, 'https://vinacascara.lovable.app/assets/vina-cascara-2-B4YfnOpF.png', 'Trà Cascara Dạng Túi Lọc', 0, 1),
(2, 'https://vinacascara.lovable.app/assets/vina-cascara-1-CStrgfSG.png', 'Trà Cascara Dạng Túi Lọc 2', 1, 0),
(3, 'https://vinacascara.lovable.app/assets/vina-cascara-3-BJJUCaKE.png', 'Trà Cascara Sấy và Rang', 0, 1),
(3, 'https://vinacascara.lovable.app/assets/vina-cascara-1-CStrgfSG.png', 'Trà Cascara Sấy và Rang 2', 1, 0);

-- Sample reviews
INSERT INTO `reviews` (`product_id`, `user_id`, `rating`, `title`, `comment`, `status`) VALUES
(1, 1, 5, 'Tuyệt vời!', 'Trà ngon, hương thơm tự nhiên, rất hài lòng với sản phẩm.', 'approved'),
(2, 1, 5, 'Tiện lợi và thơm ngon', 'Mình hay dùng loại túi lọc này khi đi làm, rất tiện.', 'approved'),
(3, 1, 4, 'Hương vị đậm đà', 'Thích cái mùi rang thơm phức, phù hợp với người thích vị mạnh.', 'approved');
