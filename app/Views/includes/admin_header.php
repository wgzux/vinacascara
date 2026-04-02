<?php
// Admin Header - for all admin pages
$adminUser = Auth::user();
$adminPageTitle = $adminPageTitle ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($adminPageTitle) ?> | Admin – <?= SITE_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-body">

<div class="admin-layout">
  <!-- Sidebar -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
      <div class="sidebar-logo">
        <span class="logo-main">VINA</span><span class="logo-accent">CASCARA</span>
      </div>
      <span class="sidebar-badge">Admin</span>
    </div>

    <nav class="sidebar-nav">
      <?php
      $currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
      $navItems = [
        ['href' => 'admin', 'icon' => 'grid', 'label' => 'Dashboard'],
        ['href' => 'admin/orders', 'icon' => 'package', 'label' => 'Đơn hàng'],
        ['href' => 'admin/products', 'icon' => 'box', 'label' => 'Sản phẩm'],
        ['href' => 'admin/customers', 'icon' => 'users', 'label' => 'Khách hàng'],
        ['href' => 'admin/reviews', 'icon' => 'star', 'label' => 'Đánh giá'],
      ];
      foreach ($navItems as $item):
        $isActive = $currentPath === $item['href'];
      ?>
      <a href="/<?= $item['href'] ?>" class="nav-item <?= $isActive ? 'active' : '' ?>">
        <?= adminIcon($item['icon']) ?>
        <span><?= $item['label'] ?></span>
      </a>
      <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
      <a href="/" class="nav-item" target="_blank">
        <?= adminIcon('external-link') ?>
        <span>Xem website</span>
      </a>
      <a href="/admin/logout" class="nav-item nav-item-danger">
        <?= adminIcon('log-out') ?>
        <span>Đăng xuất</span>
      </a>
    </div>
  </aside>

  <!-- Main Content -->
  <div class="admin-main">
    <!-- Top Bar -->
    <header class="admin-topbar">
      <button class="sidebar-toggle" onclick="toggleSidebar()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <div class="topbar-title"><?= e($adminPageTitle) ?></div>
      <div class="topbar-user">
        <?php if ($adminUser['avatar']): ?>
        <img src="<?= e($adminUser['avatar']) ?>" alt="Avatar" class="topbar-avatar">
        <?php else: ?>
        <div class="topbar-initials"><?= strtoupper(substr($adminUser['name'], 0, 1)) ?></div>
        <?php endif; ?>
        <span><?= e(explode(' ', $adminUser['name'])[0]) ?></span>
      </div>
    </header>

    <!-- Flash Messages -->
    <?php foreach (['success', 'error', 'info'] as $type): ?>
    <?php if ($msg = Auth::getFlash($type)): ?>
    <div class="admin-flash admin-flash-<?= $type ?>">
      <?= e($msg) ?>
      <button onclick="this.parentElement.remove()">×</button>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>

    <div class="admin-content">

<?php
function adminIcon(string $name): string {
    $icons = [
        'grid'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
        'package'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 002 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
        'box'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 002 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>',
        'users'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>',
        'star'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
        'external-link'=> '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>',
        'log-out'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
        'edit'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
        'trash'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>',
        'plus'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
        'eye'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>',
    ];
    return $icons[$name] ?? '';
}
?>
