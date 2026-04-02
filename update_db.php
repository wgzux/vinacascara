<?php
require 'config/config.php';
require 'core/Database.php';

$pdo = Database::getInstance();

try {
    $pdo->exec("ALTER TABLE products ADD COLUMN specification VARCHAR(50) NULL AFTER price");
} catch(Exception $e) {
    // column might already exist
}

// Update Product 1
$pdo->exec("UPDATE products 
    SET name='Trà Cascara Nguyên Chất', slug='tra-cascara-nguyen-chat', short_description='100% từ vỏ trái cà phê', 
        description='<ul><li>100% vỏ trái cà phê Arabica</li><li>Không chất bảo quản</li><li>Phơi thủ công trong nhà kính</li><li>Dễ pha chế</li><li>Thơm mùi trái cây khô</li></ul>',
        price=120000, compare_price=NULL, specification='100G' 
    WHERE id=1");

$pdo->exec("UPDATE product_images SET image_url='/assets/images/product-1.png' WHERE product_id=1");

// Update Product 2
$pdo->exec("UPDATE products 
    SET name='Trà Cascara Dạng Túi Lọc', slug='tra-cascara-dang-tui-loc', short_description='Tiện lợi, dễ sử dụng', 
        description='<ul><li>100% vỏ trái cà phê Arabica</li><li>Không chất bảo quản</li><li>Phơi thủ công trong nhà kính</li><li>Dễ pha chế</li><li>Thơm mùi trái cây khô</li></ul>',
        category_id=1, price=110000, compare_price=NULL, specification='HỘP 10 TÚI' 
    WHERE id=2");

$pdo->exec("UPDATE product_images SET image_url='/assets/images/product-2.png' WHERE product_id=2");

// Update Product 3
$pdo->exec("UPDATE products 
    SET name='Trà Cascara Sấy & Rang', slug='tra-cascara-say-rang', short_description='Hương vị đậm đà', 
        description='<ul><li>100% vỏ trái cà phê Arabica</li><li>Không chất bảo quản</li><li>Sấy và rang thủ công</li><li>Dễ pha chế</li><li>Thơm mùi trái cây khô và cafe</li></ul>',
        price=120000, compare_price=NULL, specification='100G' 
    WHERE id=3");

$pdo->exec("UPDATE product_images SET image_url='/assets/images/product-3.png' WHERE product_id=3");

echo "Success";
