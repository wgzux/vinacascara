<?php
/**
 * Công cụ tự động sửa lỗi phông chữ Database cho Vina Cascara
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';

echo "<h1>Đang tiến hành sửa lỗi phông chữ...</h1>";

try {
    $pdo = Database::getInstance();
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

    $queries = [
        "UPDATE products SET name = 'Trà Cascara Nguyên Chất', short_description = '100% từ vỏ trái cà phê', description = '<ul><li>100% vỏ trái cà phê Arabica</li><li>Không chất bảo quản</li><li>Phơi thủ công trong nhà kính</li><li>Dễ pha chế</li><li>Thơm mùi trái cây khô</li></ul>'  WHERE id = 1",
        "UPDATE products SET name = 'Trà Cascara Dạng Túi Lọc', short_description = 'Tiện lợi, dễ sử dụng', description = '<ul><li>100% vỏ trái cà phê Arabica</li><li>Không chất bảo quản</li><li>Phơi thủ công trong nhà kính</li><li>Dễ pha chế</li><li>Thơm mùi trái cây khô</li></ul>' WHERE id = 2",
        "UPDATE products SET name = 'Trà Cascara Sấy & Rang', short_description = 'Hương vị đậm đà', description = '<ul><li>100% vỏ trái cà phê Arabica</li><li>Không chất bảo quản</li><li>Sấy và rang thủ công</li><li>Dễ pha chế</li><li>Thơm mùi trái cây khô và cafe</li></ul>' WHERE id = 3",
        "UPDATE categories SET name = 'Trà Cascara', description = 'Bộ sưu tập trà từ vỏ trái cà phê Arabica Việt Nam' WHERE id = 1",
        "UPDATE product_images SET alt_text = 'Trà Cascara Nguyên Chất' WHERE product_id = 1",
        "UPDATE product_images SET alt_text = 'Trà Cascara Dạng Túi Lọc' WHERE product_id = 2",
        "UPDATE product_images SET alt_text = 'Trà Cascara Sấy và Rang' WHERE product_id = 3",
        "UPDATE users SET name = 'Admin Vina Cascara' WHERE role = 'admin'"
    ];

    foreach ($queries as $sql) {
        $pdo->exec($sql);
    }

    echo "<div style='color:green; font-weight:bold; font-size:20px; border:2px solid green; padding:20px;'>
            XONG! DỮ LIỆU ĐÃ ĐƯỢC CHỮA LÀNH.<br><br>
            Bạn hãy quay lại trang chủ wgzux.wuaze.com để kiểm tra kết quả nhé!
          </div>";

} catch (Exception $e) {
    echo "<div style='color:red;'>LỖI: " . $e->getMessage() . "</div>";
}
