<?php
$content = file_get_contents('public/assets/css/style.css');
$content = str_replace("\0", "", $content);
$lines = explode("\n", $content);
$cleanLines = [];
foreach ($lines as $line) {
    if (strpos($line, 'P a g e   T o p') !== false || strpos($line, 'p r o d u c t s - p a g e - s e c t i o n') !== false) {
        continue;
    }
    $cleanLines[] = rtrim($line);
}
$cleanLines[] = "/* ---- PAGE HEADER PADDING FIX ---- */";
$cleanLines[] = ".products-page-section, .cart-section, .checkout-section, .product-detail-section { padding-top: 120px; }";

file_put_contents('public/assets/css/style.css', implode("\n", $cleanLines));
echo "Fixed";
