<?php
// Header include - pass $pageTitle and $pageDescription before including
$pageTitle = $pageTitle ?? SITE_NAME;
$pageDescription = $pageDescription ?? 'Trà Cascara từ vỏ cà phê Arabica Việt Nam';
$cartCount = Cart::count();
$currentUser = Auth::user();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> | <?= SITE_NAME ?></title>
  <meta name="description" content="<?= e($pageDescription) ?>">
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($pageDescription) ?>">
  <meta property="og:image" content="https://vinacascara.lovable.app/assets/vina-cascara-1-CStrgfSG.png">
  <link rel="icon" href="/assets/images/favicon.png" type="image/png">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
  <!-- Main CSS -->
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<!-- Navigation -->
<nav class="navbar" id="navbar">
  <div class="nav-container">
    <a href="/" class="nav-logo">
      <span class="logo-text">VINA<span class="logo-accent">CASCARA</span></span>
    </a>

    <ul class="nav-links" id="navLinks">
      <li><a href="/" class="nav-link">Trang Chủ</a></li>
      <li><a href="/products" class="nav-link">Sản Phẩm</a></li>
      <li><a href="/#about" class="nav-link">Về Chúng Tôi</a></li>
      <li><a href="/#contact" class="nav-link">Liên Hệ</a></li>
    </ul>

    <div class="nav-actions">
      <a href="/cart" class="nav-cart" title="Giỏ hàng">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
          <line x1="3" y1="6" x2="21" y2="6"/>
          <path d="M16 10a4 4 0 01-8 0"/>
        </svg>
        <?php if ($cartCount > 0): ?>
          <span class="cart-badge"><?= $cartCount ?></span>
        <?php endif; ?>
      </a>

      <?php if ($currentUser): ?>
        <div class="nav-user" id="userDropdown">
          <button class="user-btn" onclick="toggleUserMenu()">
            <?php if ($currentUser['avatar']): ?>
              <img src="<?= e($currentUser['avatar']) ?>" alt="Avatar" class="user-avatar">
            <?php else: ?>
              <div class="user-initials"><?= strtoupper(substr($currentUser['name'], 0, 1)) ?></div>
            <?php endif; ?>
            <span class="user-name"><?= e(explode(' ', $currentUser['name'])[0]) ?></span>
            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </button>
          <div class="user-menu" id="userMenu">
            <?php if ($currentUser['role'] === 'admin'): ?>
              <a href="/admin" class="user-menu-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Dashboard Admin
              </a>
            <?php endif; ?>
            <a href="/my-orders" class="user-menu-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
              Đơn hàng của tôi
            </a>
            <hr class="menu-divider">
            <a href="/logout" class="user-menu-item text-danger">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
              Đăng xuất
            </a>
          </div>
        </div>
      <?php else: ?>
        <a href="/login" class="btn btn-outline btn-sm">Đăng nhập</a>
      <?php endif; ?>

      <button class="nav-toggle" id="navToggle" onclick="toggleMobileNav()" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</nav>

<!-- Flash Messages -->
<?php foreach (['success', 'error', 'info', 'warning'] as $type): ?>
  <?php if ($msg = Auth::getFlash($type)): ?>
    <div class="flash flash-<?= $type ?>" id="flashMsg">
      <span><?= e($msg) ?></span>
      <button onclick="this.parentElement.remove()">×</button>
    </div>
  <?php endif; ?>
<?php endforeach; ?>

<main>
