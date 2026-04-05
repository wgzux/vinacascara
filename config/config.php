<?php
// ========================================
// VINA CASCARA - MAIN CONFIGURATION
// ========================================

// Detect environment
$isLocal = ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1' || $_SERVER['HTTP_HOST'] === 'localhost');
$isInfinityFree = strpos($_SERVER['HTTP_HOST'] ?? '', 'epizy.com') !== false || strpos($_SERVER['HTTP_HOST'] ?? '', 'infinityfreeapp.com') !== false;

// Database Configuration
// Note: On InfinityFree, you MUST use the host provided in your control panel (e.g., sql123.epizy.com)
if ($isLocal) {
    // Cấu hình cho máy cá nhân (XAMPP / Laragon)
    define('DB_HOST', '127.0.0.1');
    define('DB_PORT', '3306');
    define('DB_NAME', 'cascara_db'); // Tên database bạn tạo ở Local
    define('DB_USER', 'root');      // Mặc định của XAMPP là root
    define('DB_PASS', '');          // Mặc định của XAMPP là để trống
} else {
    // Cấu hình cho Hosting InfinityFree (Giữ nguyên cái cũ)
    define('DB_HOST', 'sql308.infinityfree.com');
    define('DB_PORT', '3306');
    define('DB_NAME', 'if0_41565143_cascara_db');
    define('DB_USER', 'if0_41565143');
    define('DB_PASS', '5XE0YWPhNnnejh');
}

// Site Configuration
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('SITE_URL', "{$protocol}://{$host}");
define('SITE_NAME', 'Vina Cascara');
define('SITE_TAGLINE', 'Trà từ vỏ cà phê Arabica Việt Nam');

// Google OAuth (Requires https on production)
define('GOOGLE_CLIENT_ID', $_ENV['GOOGLE_CLIENT_ID'] ?? '');
define('GOOGLE_CLIENT_SECRET', $_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
define('GOOGLE_REDIRECT_URI', SITE_URL . '/auth/google/callback');

// VNPay Configuration (Sandbox by default)
define('VNPAY_TMN_CODE', $_ENV['VNPAY_TMN_CODE'] ?? 'TESTCODE');
define('VNPAY_HASH_SECRET', $_ENV['VNPAY_HASH_SECRET'] ?? 'TESTSECRET');
define('VNPAY_URL', $_ENV['VNPAY_URL'] ?? 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
define('VNPAY_RETURN_URL', SITE_URL . '/vnpay/return');

// Upload directory (Absolute path for InfinityFree)
define('UPLOAD_DIR', __DIR__ . '/../public/assets/uploads/');
define('UPLOAD_URL', '/assets/uploads/');

// Application settings
define('APP_DEBUG', true); // Force debug mode to see errors on InfinityFree
define('ITEMS_PER_PAGE', 12);
define('SHIPPING_FEE', 30000);
define('FREE_SHIPPING_THRESHOLD', 300000);

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
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
