<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';

if (!function_exists('e')) {
    function e(string $str): string { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('redirect')) {
    function redirect(string $url): void { header('Location: ' . $url); exit; }
}

Auth::requireAdmin();
$adminPageTitle = 'Cài đặt tài khoản';
$user = Auth::user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Lấy thông tin user hiện tại từ DB để kiểm tra mật khẩu
    $dbUser = Database::fetchOne("SELECT password FROM users WHERE id = ?", [$user['id']]);

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        Auth::setFlash('error', 'Vui lòng điền đầy đủ thông tin.');
    } elseif (!password_verify($currentPassword, $dbUser['password'])) {
        Auth::setFlash('error', 'Mật khẩu hiện tại không chính xác.');
    } elseif ($newPassword !== $confirmPassword) {
        Auth::setFlash('error', 'Mật khẩu mới không khớp.');
    } elseif (strlen($newPassword) < 6) {
        Auth::setFlash('error', 'Mật khẩu mới phải có ít nhất 6 ký tự.');
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        Database::update('users', ['password' => $hashedPassword], 'id = ?', [$user['id']]);
        Auth::setFlash('success', 'Đổi mật khẩu thành công!');
        redirect('/admin/profile');
    }
}

require __DIR__ . '/../app/Views/includes/admin_header.php';
?>

<div class="admin-card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h3>Đổi mật khẩu</h3>
    </div>
    
    <div class="card-body" style="padding: 24px;">
        <div style="margin-bottom: 24px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 60px; height: 60px; background: var(--brown); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold;">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <div>
                <strong style="display: block; font-size: 1.1rem;"><?= e($user['name']) ?></strong>
                <span class="text-muted"><?= e($user['email']) ?></span>
            </div>
        </div>

        <form method="POST" class="admin-form">
            <div class="form-group">
                <label>Mật khẩu hiện tại</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu mới</label>
                <input type="password" name="new_password" required minlength="6">
            </div>
            <div class="form-group">
                <label>Nhập lại mật khẩu mới</label>
                <input type="password" name="confirm_password" required minlength="6">
            </div>
            
            <div class="form-actions" style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../app/Views/includes/admin_footer.php'; ?>
