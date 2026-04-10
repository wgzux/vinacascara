<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

try {
    $newPassword = password_hash('Admin@123', PASSWORD_BCRYPT);
    $email = 'admin@vinacascara.com';

    $db = Database::getInstance();
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ? AND role = 'admin'");
    $stmt->execute([$newPassword, $email]);

    if ($stmt->rowCount() > 0) {
        echo "<h1 style='color:green;'>Thành công!</h1>";
        echo "<p>Mật khẩu của admin (<b>$email</b>) đã được đặt lại thành: <b>Admin@123</b></p>";
        echo "<a href='/login'>Đi tới trang đăng nhập</a>";
    } else {
        echo "<h1 style='color:red;'>Thất bại!</h1>";
        echo "<p>Không tìm thấy tài khoản admin với email này trong database.</p>";
    }
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage();
}
