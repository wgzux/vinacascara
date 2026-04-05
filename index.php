<?php
/**
 * VINA CASCARA - Main Bridge for InfinityFree
 * Place this file directly inside 'htdocs'.
 */

// Enable error reporting to catch issues
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Root bridge to the main engine
$publicIndex = __DIR__ . '/public/index.php';

if (file_exists($publicIndex)) {
    require $publicIndex;
} else {
    die("<h3>Lỗi hệ thống!</h3><p>Không tìm thấy file <b>public/index.php</b> trong thư mục htdocs.</p>");
}
