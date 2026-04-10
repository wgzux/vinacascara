<?php
/**
 * VINA CASCARA - Main Engine
 * Location: public/index.php
 */

// Debugging for InfinityFree
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Base Path Definition - Explicit for Shared Hosting
// Since this file is in htdocs/public, BASE_PATH should be htdocs
define('BASE_PATH', dirname(__DIR__));

// Robust Autoloader for Shared Hosting (Combating Linux Case-Sensitivity)
spl_autoload_register(function ($class) {
    // Fix for Linux case-sensitivity: change 'App\' to 'app/'
    $classPath = str_replace('\\', '/', $class);
    if (strpos($classPath, 'App/') === 0) {
        $classPath = 'app' . substr($classPath, 3);
    }
    
    $candidates = [
        BASE_PATH . '/' . $classPath . '.php', // Standard: App/Controllers/HomeController.php
        BASE_PATH . '/' . str_replace('App/', 'app/', $classPath) . '.php', // Lowercase app
        BASE_PATH . '/' . str_replace('Core/', 'core/', $classPath) . '.php', // Lowercase core
        BASE_PATH . '/' . str_replace('App/Controllers/', 'app/controllers/', $classPath) . '.php', // Lowercase app/controllers
        BASE_PATH . '/' . strtolower(str_replace('App/', 'app/', $classPath)) . '.php', // Full lowercase
    ];

    foreach ($candidates as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // Diagnostic Fallback
    die("<div style='background:#f9f2f4;color:#a94442;padding:25px;border:2px solid #ebccd1;border-radius:10px;margin:30px;font-family:sans-serif;'>
        <h2 style='margin-top:0;'>⚠️ Lỗi: Không tìm thấy file hệ thống (Class Not Found)</h2>
        <p>Class <b>$class</b> đang được sử dụng nhưng máy chủ Linux không tìm thấy tệp tương ứng.</p>
        <p>Đây là lỗi do sự phân biệt chữ Hoa/chữ Thường (Case-sensitivity) trên InfinityFree.</p>
        <p>Tôi đã thử tìm ở tất cả các đường dẫn sau nhưng không có:</p>
        <ul style='background:#fff;padding:15px 30px;border-radius:5px;border:1px solid #ccc;'>" . 
        implode('', array_map(function($c) { return "<li><code>$c</code></li>"; }, $candidates)) . 
        "</ul>
        <p><b>Cách khắc phục:</b> Hãy kiểm tra kỹ tên Thư mục và File trong File Manager. (Vd: Phải là <code>app/Controllers/HomeController.php</code>).</p>
        </div>");
});

// Helper function for safe requiring (within htdocs only)
function safe_require($path) {
    if (file_exists($path)) {
        require_once $path;
    } else {
        $filename = basename($path);
        $folder = basename(dirname($path));
        die("<div style='background:#f9f2f4;color:#a94442;padding:25px;border:2px solid #ebccd1;border-radius:10px;margin:30px;font-family:sans-serif;'>
            <h2 style='margin-top:0;'>⚠️ Oops! Thiếu file quan trọng</h2>
            <p>Không tìm thấy tệp: <b>$filename</b> bên trong thư mục <b>$folder</b>.</p>
            <p><b>Cách khắc phục:</b> Hãy đảm bảo bạn đã upload toàn bộ thư mục <code>$folder</code> vào bên trong <b>htdocs</b> trên host.</p>
            <hr>
            <p><small>Đường dẫn hệ thống: <code>$path</code></small></p>
            </div>");
    }
}

// 1. Require Config First
require_once BASE_PATH . '/config/config.php';

// 2. Require Core Components
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Cart.php';
require_once BASE_PATH . '/core/VNPay.php';
require_once BASE_PATH . '/core/GoogleAuth.php';
require_once BASE_PATH . '/core/Router.php';

// 3. Initialize DB
Database::getInstance();

// 4. Helpers
function e($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }
function redirect($url) { header('Location: ' . $url); exit; }
function formatPrice($price) { return number_format((float)$price, 0, ',', '.') . '₫'; }

// 5. View Loader
function view($v, $data = []) {
    extract($data);
    $p = BASE_PATH . '/app/Views/' . $v . '.php';
    if (file_exists($p)) { require $p; } 
    else { die("View not found: $v"); }
}

// 6. Routing
$router = new \Core\Router();
require_once BASE_PATH . '/routes/web.php';
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
