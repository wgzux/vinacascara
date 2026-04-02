<?php

class Migrator {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function run() {
        // Create users table
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NULL,
            phone VARCHAR(20) NULL,
            avatar VARCHAR(255) NULL,
            google_id VARCHAR(100) NULL UNIQUE,
            role ENUM('customer', 'admin') DEFAULT 'customer',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Create categories table
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE
        )");

        // Create products table
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            category_id INT NULL,
            name VARCHAR(200) NOT NULL,
            slug VARCHAR(200) NOT NULL UNIQUE,
            short_description TEXT,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            compare_price DECIMAL(10,2) NULL,
            stock INT DEFAULT 0,
            featured TINYINT(1) DEFAULT 0,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        )");

        // Create product_images table
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS product_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            image_url VARCHAR(255) NOT NULL,
            alt_text VARCHAR(100) NULL,
            is_primary TINYINT(1) DEFAULT 0,
            sort_order INT DEFAULT 0,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )");

        // Create cart table
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            session_id VARCHAR(100) NULL,
            product_id INT NOT NULL,
            quantity INT DEFAULT 1,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )");

        // Create orders table
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_code VARCHAR(50) NOT NULL UNIQUE,
            user_id INT NULL,
            guest_name VARCHAR(100) NULL,
            guest_email VARCHAR(100) NULL,
            guest_phone VARCHAR(20) NULL,
            shipping_name VARCHAR(100) NOT NULL,
            shipping_phone VARCHAR(20) NOT NULL,
            shipping_address TEXT NOT NULL,
            shipping_province VARCHAR(100) NULL,
            shipping_district VARCHAR(100) NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            shipping_fee DECIMAL(10,2) DEFAULT 0,
            discount DECIMAL(10,2) DEFAULT 0,
            total DECIMAL(10,2) NOT NULL,
            payment_method ENUM('cod', 'vnpay') DEFAULT 'cod',
            payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
            order_status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
            vnpay_transaction_no VARCHAR(100) NULL,
            notes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )");

        // Create order_items table
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NULL,
            product_name VARCHAR(200) NOT NULL,
            product_image VARCHAR(255) NULL,
            price DECIMAL(10,2) NOT NULL,
            quantity INT NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
        )");

        // Create reviews table
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            user_id INT NOT NULL,
            rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            title VARCHAR(150) NULL,
            comment TEXT NULL,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");

        $this->seedData();
    }

    private function seedData() {
        // Seed Admin User
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute(['admin@vinacascara.com']);
        if (!$stmt->fetch()) {
            $hashed = password_hash('Admin@123', PASSWORD_DEFAULT);
            $this->pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Administrator', 'admin@vinacascara.com', '$hashed', 'admin')");
        }

        // Seed Categories
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM categories");
        if ($stmt->fetchColumn() == 0) {
            $this->pdo->exec("INSERT INTO categories (name, slug) VALUES 
                ('Trà Cascara Nguyên Bản', 'tra-cascara-nguyen-ban'),
                ('Trà Cascara Trái Cây', 'tra-cascara-trai-cay'),
                ('Phụ kiện pha trà', 'phu-kien-pha-tra')");
        }

        // Seed Product
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM products");
        if ($stmt->fetchColumn() == 0) {
            $this->pdo->exec("INSERT INTO products (category_id, name, slug, short_description, description, price, compare_price, stock, featured) VALUES 
                (1, 'Trà Cascara Arabica Đặc Sản', 'tra-cascara-arabica-dac-san', 'Hương vị trái cây nhiệt đới, hậu vị ngọt kéo dài.', '<p>Sản phẩm được làm từ vỏ quả cà phê Arabica chín mọng nguyên chất.</p>', 150000, 180000, 100, 1),
                (1, 'Trà Cascara Lên Men Thùng Gỗ', 'tra-cascara-len-men', 'Phương pháp lên men đặc biệt cho hương vị sâu lắng.', '<p>Hương vani, gỗ sồi và cherry ngâm rượu.</p>', 250000, NULL, 50, 1),
                (2, 'Cascara Dâu Tây Nhiệt Đới', 'cascara-dau-tay', 'Sự pha trộn hoàn hảo giữa Cascara và dâu tây sấy lạnh.', '<p>Giải nhiệt mùa hè cực tốt.</p>', 180000, 200000, 80, 0)");
                
            $this->pdo->exec("INSERT INTO product_images (product_id, image_url, is_primary) VALUES 
                (1, 'https://vinacascara.lovable.app/assets/cascara-tea-DWQd2tY1.png', 1),
                (2, 'https://vinacascara.lovable.app/assets/cascara-pour-Bvj6o8K1.png', 1),
                (3, 'https://vinacascara.lovable.app/assets/vina-cascara-1-CStrgfSG.png', 1)");
        }
    }
}
