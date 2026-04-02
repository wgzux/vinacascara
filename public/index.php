<?php
// Serve static files when using PHP built-in server
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if ($path && is_file($path)) {
        return false;
    }
}

// Enable Error Reporting for dev
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Base Path Definition
define('BASE_PATH', dirname(__DIR__));

// Simple Autoloader
spl_autoload_register(function ($class) {
    // Convert namespace to path App\Controllers\HomeController -> App/Controllers/HomeController.php
    $file = BASE_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    // Match App to app (lowercase folder)
    $file = str_replace(BASE_PATH . '/App', BASE_PATH . '/app', $file);
    $file = str_replace(BASE_PATH . '/Core', BASE_PATH . '/core', $file);
    if (file_exists($file)) {
        require_once $file;
    }
});

// Require Configuration
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Cart.php';
require_once BASE_PATH . '/core/VNPay.php';
require_once BASE_PATH . '/core/Router.php';

// Check database initialization
Database::getInstance();

// Load Helpers
function e(string $str): string { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
function redirect(string $url): void { header('Location: ' . $url); exit; }
function formatPrice(float $price): string { return number_format($price, 0, ',', '.') . '₫'; }

// Simple basic View Loader
function view(string $viewPath, array $data = []) {
    extract($data);
    require BASE_PATH . '/app/Views/' . $viewPath . '.php';
}

// Router dispatch
$router = new \Core\Router();
require BASE_PATH . '/routes/web.php';

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
