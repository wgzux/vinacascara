<?php
namespace App\Controllers;

use Auth;
use Database;

class AuthController {
    public function login() {
        if (Auth::isLoggedIn()) {
            redirect('/');
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
        
        $client = new \Google_Client();
        $client->setClientId(GOOGLE_CLIENT_ID);
        $client->setClientSecret(GOOGLE_CLIENT_SECRET);
        $client->setRedirectUri(SITE_URL . '/auth-callback');
        $client->addScope('email');
        $client->addScope('profile');
        
        $authUrl = $client->createAuthUrl();
        redirect($authUrl);
    }

    public function authCallback() {
        if (Auth::isLoggedIn()) {
            redirect('/');
        }

        if (isset($_GET['code'])) {
            $client = new \Google_Client();
            $client->setClientId(GOOGLE_CLIENT_ID);
            $client->setClientSecret(GOOGLE_CLIENT_SECRET);
            $client->setRedirectUri(SITE_URL . '/auth-callback');

            try {
                $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
                
                if (!isset($token['error'])) {
                    $client->setAccessToken($token['access_token']);
                    $google_oauth = new \Google_Service_Oauth2($client);
                    $google_account_info = $google_oauth->userinfo->get();
                    
                    $email = $google_account_info->email;
                    $name = $google_account_info->name;
                    $google_id = $google_account_info->id;
                    $avatar = $google_account_info->picture;

                    // Check if user exists
                    $user = Database::fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
                    
                    if ($user) {
                        // Update avatar if changed
                        if ($user['avatar'] !== $avatar) {
                            Database::query("UPDATE users SET avatar = ? WHERE id = ?", [$avatar, $user['id']]);
                        }
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
                // Log error
                Auth::setFlash('error', 'Đăng nhập Google thất bại. Vui lòng thử lại.');
            }
        }
        
        redirect('/login');
    }
}
