<?php

/** @var \Core\Router $router */

$router->get('/', [App\Controllers\HomeController::class, 'index']);
$router->post('/api/contact.php', [App\Controllers\HomeController::class, 'submitContact']);
$router->get('/products', [App\Controllers\ProductController::class, 'index']);
$router->get('/product/{slug}', [App\Controllers\ProductController::class, 'show']);
$router->post('/product/{slug}', [App\Controllers\ProductController::class, 'postReview']);

$router->get('/cart', [App\Controllers\CartController::class, 'index']);
$router->post('/api/cart/add', [App\Controllers\CartController::class, 'apiAdd']);
$router->post('/api/cart/update', [App\Controllers\CartController::class, 'apiUpdate']);
$router->post('/api/cart/remove', [App\Controllers\CartController::class, 'apiRemove']);
$router->get('/api/cart/count', [App\Controllers\CartController::class, 'apiCount']);
$router->get('/checkout', [App\Controllers\CheckoutController::class, 'index']);
$router->post('/checkout', [App\Controllers\CheckoutController::class, 'process']);
$router->get('/order-success', [App\Controllers\CheckoutController::class, 'success']);
$router->get('/vnpay-return', [App\Controllers\CheckoutController::class, 'vnpayReturn']);
$router->get('/my-orders', [App\Controllers\OrderController::class, 'myOrders']);

$router->get('/login', [App\Controllers\AuthController::class, 'login']);
$router->post('/login', [App\Controllers\AuthController::class, 'login']);
$router->get('/logout', [App\Controllers\AuthController::class, 'logout']);
$router->get('/auth-google', [App\Controllers\AuthController::class, 'authGoogle']);
$router->get('/auth-callback', [App\Controllers\AuthController::class, 'authCallback']);

// Admin Dashboard & Modules
$router->get('/admin', function() { require_once BASE_PATH . '/admin/index.php'; });
$router->get('/admin/login', function() { require_once BASE_PATH . '/admin/login.php'; });
$router->post('/admin/login', function() { require_once BASE_PATH . '/admin/login.php'; });
$router->get('/admin/products', function() { require_once BASE_PATH . '/admin/products.php'; });
$router->post('/admin/products', function() { require_once BASE_PATH . '/admin/products.php'; });
$router->get('/admin/products/add', function() { require_once BASE_PATH . '/admin/product-add.php'; });
$router->post('/admin/products/add', function() { require_once BASE_PATH . '/admin/product-add.php'; });
$router->get('/admin/orders', function() { require_once BASE_PATH . '/admin/orders.php'; });
$router->post('/admin/orders', function() { require_once BASE_PATH . '/admin/orders.php'; });
$router->get('/admin/customers', function() { require_once BASE_PATH . '/admin/customers.php'; });
$router->get('/admin/reviews', function() { require_once BASE_PATH . '/admin/reviews.php'; });
$router->post('/admin/reviews', function() { require_once BASE_PATH . '/admin/reviews.php'; });
$router->get('/admin/logout', function() { require_once BASE_PATH . '/admin/logout.php'; });
