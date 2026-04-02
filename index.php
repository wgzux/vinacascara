<?php
// ========================================
// FRONT CONTROLLER - Main Entry Point
// ========================================

// Bootstrap
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/Cart.php';
require_once __DIR__ . '/core/GoogleAuth.php';
require_once __DIR__ . '/core/VNPay.php';

// Helper function
function formatPrice(float $price): string {
    return number_format($price, 0, ',', '.') . '₫';
}

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function asset(string $path): string {
    return '/assets/' . ltrim($path, '/');
}

// Parse URL
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);
$path = trim($path, '/');

// Simple router
$routes = [
    ''                      => 'pages/home.php',
    'products'              => 'pages/products.php',
    'cart'                  => 'pages/cart.php',
    'checkout'              => 'pages/checkout.php',
    'order-success'         => 'pages/order-success.php',
    'my-orders'             => 'pages/my-orders.php',
    'login'                 => 'pages/login.php',
    'logout'                => 'pages/logout.php',
    'auth/google'           => 'pages/auth-google.php',
    'auth/google/callback'  => 'pages/auth-callback.php',
    'vnpay/return'          => 'pages/vnpay-return.php',
    'admin'                 => 'admin/index.php',
    'admin/login'           => 'admin/login.php',
    'admin/products'        => 'admin/products.php',
    'admin/products/add'    => 'admin/product-add.php',
    'admin/orders'          => 'admin/orders.php',
    'admin/customers'       => 'admin/customers.php',
    'admin/reviews'         => 'admin/reviews.php',
    'admin/logout'          => 'admin/logout.php',
];

// API routes
if (str_starts_with($path, 'api/')) {
    $apiFile = __DIR__ . '/' . $path . '.php';
    if (file_exists($apiFile)) {
        require $apiFile;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
    exit;
}

// Product detail
if (preg_match('/^product\/([a-z0-9\-]+)$/', $path, $matches)) {
    $_GET['slug'] = $matches[1];
    require __DIR__ . '/pages/product-detail.php';
    exit;
}

// Admin product actions with ID
if (preg_match('/^admin\/products\/edit\/(\d+)$/', $path, $matches)) {
    $_GET['id'] = $matches[1];
    require __DIR__ . '/admin/product-edit.php';
    exit;
}

if (preg_match('/^admin\/orders\/(\d+)$/', $path, $matches)) {
    $_GET['id'] = $matches[1];
    require __DIR__ . '/admin/order-detail.php';
    exit;
}

// Match route
if (isset($routes[$path])) {
    $file = __DIR__ . '/' . $routes[$path];
    if (file_exists($file)) {
        require $file;
        exit;
    }
}

// 404
http_response_code(404);
require __DIR__ . '/pages/404.php';
