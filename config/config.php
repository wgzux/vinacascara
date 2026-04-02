<?php
// ========================================
// VINA CASCARA - MAIN CONFIGURATION
// ========================================

// Detect environment
$isRailway = isset($_ENV['RAILWAY_ENVIRONMENT']) || isset($_SERVER['RAILWAY_ENVIRONMENT']);

// Database Configuration
define('DB_HOST', $_ENV['MYSQLHOST'] ?? $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['MYSQLPORT'] ?? $_ENV['DB_PORT'] ?? '3306');
define('DB_NAME', $_ENV['MYSQLDATABASE'] ?? $_ENV['DB_NAME'] ?? 'vinacascara');
define('DB_USER', $_ENV['MYSQLUSER'] ?? $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['MYSQLPASSWORD'] ?? $_ENV['DB_PASS'] ?? '');

// Site Configuration
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('SITE_URL', $_ENV['SITE_URL'] ?? "{$protocol}://{$host}");
define('SITE_NAME', 'Vina Cascara');
define('SITE_TAGLINE', 'Trà từ vỏ cà phê Arabica Việt Nam');

// Google OAuth
define('GOOGLE_CLIENT_ID', $_ENV['GOOGLE_CLIENT_ID'] ?? '');
define('GOOGLE_CLIENT_SECRET', $_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
define('GOOGLE_REDIRECT_URI', SITE_URL . '/auth-callback');

// VNPay Configuration (Sandbox by default)
define('VNPAY_TMN_CODE', $_ENV['VNPAY_TMN_CODE'] ?? 'TESTCODE');
define('VNPAY_HASH_SECRET', $_ENV['VNPAY_HASH_SECRET'] ?? 'TESTSECRET');
define('VNPAY_URL', $_ENV['VNPAY_URL'] ?? 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
define('VNPAY_RETURN_URL', SITE_URL . '/vnpay/return');

// Upload directory
define('UPLOAD_DIR', __DIR__ . '/../public/assets/uploads/');
define('UPLOAD_URL', '/assets/uploads/');

// Application settings
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('ITEMS_PER_PAGE', 12);
define('SHIPPING_FEE', 30000); // Default shipping fee
define('FREE_SHIPPING_THRESHOLD', 300000); // Free shipping above this amount

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    if ($isRailway) {
        ini_set('session.cookie_secure', 1);
        ini_set('session.cookie_samesite', 'Lax');
    }
    session_start();
}
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
