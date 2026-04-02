<?php
// ========================================
// GOOGLE OAUTH 2.0 - No external library needed
// ========================================
class GoogleAuth {
    private static string $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth';
    private static string $tokenUrl = 'https://oauth2.googleapis.com/token';
    private static string $userInfoUrl = 'https://www.googleapis.com/oauth2/v3/userinfo';

    public static function getAuthUrl(): string {
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;

        $params = http_build_query([
            'client_id'     => GOOGLE_CLIENT_ID,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'access_type'   => 'online',
            'prompt'        => 'select_account',
        ]);
        return self::$authUrl . '?' . $params;
    }

    public static function handleCallback(string $code, string $state): ?array {
        // Verify state
        if ($state !== ($_SESSION['oauth_state'] ?? '')) {
            return null;
        }
        unset($_SESSION['oauth_state']);

        // Exchange code for token
        $token = self::exchangeCode($code);
        if (!$token || empty($token['access_token'])) {
            return null;
        }

        // Get user info
        return self::getUserInfo($token['access_token']);
    }

    private static function exchangeCode(string $code): ?array {
        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'client_id'     => GOOGLE_CLIENT_ID,
                    'client_secret' => GOOGLE_CLIENT_SECRET,
                    'redirect_uri'  => GOOGLE_REDIRECT_URI,
                    'grant_type'    => 'authorization_code',
                    'code'          => $code,
                ]),
                'ignore_errors' => true,
            ]
        ]);
        $response = file_get_contents(self::$tokenUrl, false, $context);
        return $response ? json_decode($response, true) : null;
    }

    private static function getUserInfo(string $accessToken): ?array {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'Authorization: Bearer ' . $accessToken,
                'ignore_errors' => true,
            ]
        ]);
        $response = file_get_contents(self::$userInfoUrl, false, $context);
        return $response ? json_decode($response, true) : null;
    }
}
