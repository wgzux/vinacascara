<?php
// Admin Login Page (separate from customer login)
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Cart.php';

function e(string $str): string { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
function redirect(string $url): void { header('Location: ' . $url); exit; }

if (Auth::isAdmin()) redirect('/admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if (Auth::attemptAdmin($email, $pass)) {
        redirect('/admin');
    } else {
        $error = 'Email hoặc mật khẩu không đúng.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login – Vina Cascara</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/admin.css">
  <style>
    body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #0f1117; }
    .login-card { background: #1a1d27; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 48px 40px; width: 100%; max-width: 400px; }
    .login-logo { text-align: center; margin-bottom: 32px; }
    .login-logo .logo { font-size: 1.4rem; font-weight: 800; letter-spacing: 3px; color: white; }
    .login-logo .logo span { color: #C4956A; }
    .login-logo p { font-size: 0.85rem; color: #64748b; margin-top: 6px; }
    .login-title { font-size: 1.3rem; font-weight: 600; text-align: center; margin-bottom: 28px; color: white; }
    .error-box { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #fca5a5; padding: 12px 16px; border-radius: 8px; font-size: 0.875rem; margin-bottom: 20px; }
  </style>
</head>
<body>
<div class="login-card">
  <div class="login-logo">
    <div class="logo">VINA<span>CASCARA</span></div>
    <p>Admin Dashboard</p>
  </div>
  <h2 class="login-title">Đăng nhập quản trị</h2>
  <?php if (!empty($error)): ?>
  <div class="error-box"><?= e($error) ?></div>
  <?php endif; ?>
  <form method="POST">
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" required placeholder="admin@vinacascara.com" autofocus>
    </div>
    <div class="form-group">
      <label>Mật khẩu</label>
      <input type="password" name="password" required placeholder="••••••••">
    </div>
    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px;margin-top:8px">
      Đăng nhập
    </button>
  </form>
  <p style="text-align:center;margin-top:20px;font-size:0.8rem;color:#64748b;">
    <a href="/" style="color:#C4956A">← Về trang chủ</a>
  </p>
</div>
</body>
</html>
