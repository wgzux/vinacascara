<?php
// ========================================
// AUTH CLASS - Session & Role Management
// ========================================
class Auth {
    
    public static function login(array $user): void {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_avatar'] = $user['avatar'] ?? null;
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        session_regenerate_id(true);
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        session_start();
    }

    public static function isLoggedIn(): bool {
        return !empty($_SESSION['logged_in']) && !empty($_SESSION['user_id']);
    }

    public static function isAdmin(): bool {
        return self::isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
    }

    public static function requireLogin(string $redirect = '/login'): void {
        if (!self::isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . $redirect);
            exit;
        }
    }

    public static function requireAdmin(): void {
        if (!self::isAdmin()) {
            if (!self::isLoggedIn()) {
                header('Location: /login');
            } else {
                http_response_code(403);
                include __DIR__ . '/../pages/403.php';
            }
            exit;
        }
    }

    public static function user(): ?array {
        if (!self::isLoggedIn()) return null;
        return [
            'id'     => $_SESSION['user_id'],
            'name'   => $_SESSION['user_name'],
            'email'  => $_SESSION['user_email'],
            'avatar' => $_SESSION['user_avatar'] ?? null,
            'role'   => $_SESSION['user_role'],
        ];
    }

    public static function id(): ?int {
        return self::isLoggedIn() ? (int) $_SESSION['user_id'] : null;
    }

    // Login with email/password
    public static function attempt(string $email, string $password): bool {
        $user = Database::fetchOne(
            "SELECT * FROM users WHERE email = ? AND role != 'admin' LIMIT 1",
            [$email]
        );
        if ($user && $user['password'] && password_verify($password, $user['password'])) {
            self::login($user);
            return true;
        }
        return false;
    }

    // Admin login
    public static function attemptAdmin(string $email, string $password): bool {
        $user = Database::fetchOne(
            "SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1",
            [$email]
        );
        if ($user && $user['password'] && password_verify($password, $user['password'])) {
            self::login($user);
            return true;
        }
        return false;
    }

    // Find or create user from Google OAuth data
    public static function loginWithGoogle(array $googleUser): array {
        $user = Database::fetchOne(
            "SELECT * FROM users WHERE google_id = ? OR email = ? LIMIT 1",
            [$googleUser['sub'], $googleUser['email']]
        );

        if ($user) {
            // Update Google ID if not set
            if (empty($user['google_id'])) {
                Database::update('users', 
                    ['google_id' => $googleUser['sub'], 'avatar' => $googleUser['picture'] ?? null],
                    'id = ?', [$user['id']]
                );
            }
        } else {
            // Create new user
            $id = Database::insert('users', [
                'name'          => $googleUser['name'],
                'email'         => $googleUser['email'],
                'avatar'        => $googleUser['picture'] ?? null,
                'google_id'     => $googleUser['sub'],
                'role'          => 'customer',
                'email_verified'=> 1,
            ]);
            $user = Database::fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
        }

        self::login($user);
        return $user;
    }

    // Flash messages
    public static function setFlash(string $type, string $message): void {
        $_SESSION['flash'][$type] = $message;
    }

    public static function getFlash(string $type): ?string {
        $msg = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $msg;
    }

    public static function hasFlash(string $type): bool {
        return isset($_SESSION['flash'][$type]);
    }

    public static function csrf(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(): bool {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}
