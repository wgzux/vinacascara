<?php
/**
 * Database Migration Script for Railway Deployment
 * This script will:
 * 1. Initialize the database schema if tables don't exist.
 * 2. Apply product updates from update_db.php.
 */

// Load Configuration
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../core/Database.php';

echo "🚀 Starting Database Migration...\n";

try {
    $pdo = Database::getInstance();
    
    // Check if the products table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
    $tableExists = $stmt->rowCount() > 0;

    if (!$tableExists) {
        echo "📂 Database schema not found. Initializing from database/schema.sql...\n";
        $sql = file_get_contents(__DIR__ . '/schema.sql');
        
        // Execute schema SQL (split by semicolon to handle multiple statements)
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        echo "✅ Database schema initialized successfully.\n";
    } else {
        echo "ℹ️ Database schema already exists. Skipping initialization.\n";
    }

    // Apply product updates and specifications (update_db.php logic)
    echo "🔄 Applying product data updates...\n";
    
    // Add specification column if not exists
    try {
        $pdo->exec("ALTER TABLE products ADD COLUMN specification VARCHAR(50) NULL AFTER price");
    } catch (Exception $e) {
        // Silently fail if column already exists
    }

    // Update Product Data (Consolidated from update_db.php)
    $updates = [
        [
            'id' => 1,
            'name' => 'Trà Cascara Nguyên Chất',
            'slug' => 'tra-cascara-nguyen-chat',
            'short' => '100% từ vỏ trái cà phê',
            'desc' => '<ul><li>100% vỏ trái cà phê Arabica</li><li>Không chất bảo quản</li><li>Phơi thủ công trong nhà kính</li><li>Dễ pha chế</li><li>Thơm mùi trái cây khô</li></ul>',
            'price' => 120000,
            'spec' => '100G',
            'image' => '/assets/images/product-1.png'
        ],
        [
            'id' => 2,
            'name' => 'Trà Cascara Dạng Túi Lọc',
            'slug' => 'tra-cascara-dang-tui-loc',
            'short' => 'Tiện lợi, dễ sử dụng',
            'desc' => '<ul><li>100% vỏ trái cà phê Arabica</li><li>Không chất bảo quản</li><li>Phơi thủ công trong nhà kính</li><li>Dễ pha chế</li><li>Thơm mùi trái cây khô</li></ul>',
            'price' => 110000,
            'spec' => 'HỘP 10 TÚI',
            'image' => '/assets/images/product-2.png'
        ],
        [
            'id' => 3,
            'name' => 'Trà Cascara Sấy & Rang',
            'slug' => 'tra-cascara-say-rang',
            'short' => 'Hương vị đậm đà',
            'desc' => '<ul><li>100% vỏ trái cà phê Arabica</li><li>Không chất bảo quản</li><li>Sấy và rang thủ công</li><li>Dễ pha chế</li><li>Thơm mùi trái cây khô và cafe</li></ul>',
            'price' => 120000,
            'spec' => '100G',
            'image' => '/assets/images/product-3.png'
        ]
    ];

    foreach ($updates as $p) {
        $stmt = $pdo->prepare("UPDATE products SET 
            name = :name, 
            slug = :slug, 
            short_description = :short, 
            description = :desc, 
            price = :price, 
            specification = :spec 
            WHERE id = :id");
        $stmt->execute([
            ':name' => $p['name'],
            ':slug' => $p['slug'],
            ':short' => $p['short'],
            ':desc' => $p['desc'],
            ':price' => $p['price'],
            ':spec' => $p['spec'],
            ':id' => $p['id']
        ]);
        
        $pdo->prepare("UPDATE product_images SET image_url = :image WHERE product_id = :id")
            ->execute([':image' => $p['image'], ':id' => $p['id']]);
    }

    echo "✨ Migration completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Error during migration: " . $e->getMessage() . "\n";
    exit(1);
}
