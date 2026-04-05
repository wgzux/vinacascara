<?php
namespace App\Controllers;

use Auth;
use Database;

class AuthController {
    public function login() {
        if (Auth::isLoggedIn()) {
            redirect('/');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Try admin login first
            if (Auth::attemptAdmin($email, $password)) {
                Auth::setFlash('success', 'Chào mừng Admin trở lại!');
                redirect('/admin');
            }
            
            // Then regular user login
            if (Auth::attempt($email, $password)) {
                Auth::setFlash('success', 'Đăng nhập thành công!');
                redirect($_SESSION['redirect_after_login'] ?? '/');
            }

            Auth::setFlash('error', 'Email hoặc mật khẩu không chính xác.');
        }

        return view('login', ['pageTitle' => 'Đăng Nhập']);
    }

    public function logout() {
        Auth::logout();
        redirect('/');
    }

    public function authGoogle() {
        if (Auth::isLoggedIn()) {
            redirect('/');
        }
        
        $authUrl = \GoogleAuth::getAuthUrl();
        redirect($authUrl);
    }

    public function authCallback() {
        if (Auth::isLoggedIn()) {
            redirect('/');
        }

        if (isset($_GET['code'])) {
            try {
                $userData = \GoogleAuth::handleCallback($_GET['code'], $_GET['state'] ?? '');
                
                if ($userData) {
                    $email = $userData['email'];
                    $name = $userData['name'];
                    $google_id = $userData['sub'];
                    $avatar = $userData['picture'] ?? null;

                    // Check if user exists by google_id or email
                    $user = Database::fetchOne("SELECT * FROM users WHERE google_id = ? OR email = ? LIMIT 1", [$google_id, $email]);
                    
                    if ($user) {
                        // Update avatar and google_id if it was missing
                        Database::query("UPDATE users SET avatar = ?, google_id = ? WHERE id = ?", [$avatar, $google_id, $user['id']]);
                        Auth::login($user);
                    } else {
                        // Register new user
                        $userId = Database::insert('users', [
                            'name' => $name,
                            'email' => $email,
                            'google_id' => $google_id,
                            'avatar' => $avatar,
                            'role' => 'customer'
                        ]);
                        
                        $newUser = Database::fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
                        Auth::login($newUser);
                    }

                    Auth::setFlash('success', 'Đăng nhập thành công!');
                    redirect($_SESSION['redirect_after_login'] ?? '/');
                }
            } catch (\Exception $e) {
                Auth::setFlash('error', 'Đăng nhập Google thất bại: ' . $e->getMessage());
            }
        }
        
        redirect('/login');
    }
}
