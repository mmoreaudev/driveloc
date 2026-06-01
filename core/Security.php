<?php
declare(strict_types=1);

final class Security
{
    public static function csrfToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="'
            . self::csrfToken()
            . '">';
    }

    public static function verifyCsrf(): void
    {
        $submitted = $_POST['csrf_token'] ?? '';

        if (!is_string($submitted) || !hash_equals(self::csrfToken(), $submitted)) {
            http_response_code(403);
            die('Requête invalide – jeton CSRF manquant ou incorrect.');
        }
    }

    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function requireLogin(): void
    {
        if (!Session::isLoggedIn()) {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            // Validation stricte : seules les URLs locales sont mémorisées
            if ($uri !== ''
                && str_starts_with($uri, '/')
                && !str_starts_with($uri, '//')
                && !str_contains($uri, 'login')
            ) {
                Session::set('_redirect_after_login', $uri);
            }
            Session::flash('error', 'Vous devez être connecté pour accéder à cette page.');
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        if (Session::get('user_status') === 'inactive') {
            Session::destroy();
            Session::flash('error', 'Votre compte a été désactivé.');
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }

    public static function requireGuest(): void
    {
        if (Session::isLoggedIn()) {
            header('Location: ' . APP_URL . '/dashboard/' . Session::userRole());
            exit;
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();

        if (!in_array(Session::userRole(), $roles, true)) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
    }

    public static function requireOwnership(int $resourceOwnerId): void
    {
        self::requireLogin();

        if (
            Session::userRole() !== 'admin'
            && Session::userId() !== $resourceOwnerId
        ) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
    }

    // Protection brute-force basée sur session
    public static function checkRateLimit(
        string $key,
        int    $maxAttempts  = 5,
        int    $decaySeconds = 300
    ): void {
        $sessionKey = '_rl_' . hash('sha256', $key);
        $data       = $_SESSION[$sessionKey] ?? ['count' => 0, 'first' => time()];

        if (time() - $data['first'] > $decaySeconds) {
            $data = ['count' => 0, 'first' => time()];
        }

        if ($data['count'] >= $maxAttempts) {
            $remaining = $decaySeconds - (time() - $data['first']);
            http_response_code(429);
            header('Retry-After: ' . $remaining);
            Session::flash('error', sprintf(
                'Trop de tentatives. Réessayez dans %d minute(s).',
                (int) ceil($remaining / 60)
            ));
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        $data['count']++;
        $_SESSION[$sessionKey] = $data;
    }

    public static function clearRateLimit(string $key): void
    {
        unset($_SESSION['_rl_' . hash('sha256', $key)]);
    }

    // Protection contre les open redirects
    public static function safeRedirect(string $url, string $fallback): string
    {
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return $url;
        }
        return $fallback;
    }
}
