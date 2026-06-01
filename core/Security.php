<?php
declare(strict_types=1);

/**
 * Security – Middleware d'authentification, protection CSRF et échappement XSS.
 *
 * ══════════════════════════════════════════════════════════════════
 *  UTILISATION DU MIDDLEWARE (résumé)
 * ══════════════════════════════════════════════════════════════════
 *
 *  // Page réservée aux utilisateurs connectés (tous rôles)
 *  Security::requireLogin();
 *
 *  // Page réservée à un ou plusieurs rôles précis
 *  Security::requireRole('admin');
 *  Security::requireRole('owner', 'admin');
 *
 *  // Page réservée aux visiteurs non connectés (ex: login, register)
 *  Security::requireGuest();
 *
 *  // Vérification CSRF sur tout formulaire POST
 *  Security::verifyCsrf();
 *
 *  // Affichage sûr (anti-XSS)
 *  echo Security::e($variable);
 *
 *  // Champ CSRF dans un formulaire
 *  echo Security::csrfField();
 * ══════════════════════════════════════════════════════════════════
 */
final class Security
{
    // ── CSRF ──────────────────────────────────────────────────────

    /**
     * Retourne le token CSRF de la session courante (le crée si absent).
     */
    public static function csrfToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    /**
     * Génère un champ <input type="hidden"> prêt à coller dans un formulaire.
     */
    public static function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="'
            . self::csrfToken()
            . '">';
    }

    /**
     * Vérifie le token CSRF soumis via POST.
     * Arrête immédiatement l'exécution avec un HTTP 403 en cas d'échec.
     */
    public static function verifyCsrf(): void
    {
        $submitted = $_POST['csrf_token'] ?? '';

        if (!is_string($submitted) || !hash_equals(self::csrfToken(), $submitted)) {
            http_response_code(403);
            die('Requête invalide – jeton CSRF manquant ou incorrect.');
        }
    }

    // ── XSS ───────────────────────────────────────────────────────

    /**
     * Échappe une valeur pour un affichage sûr dans du HTML.
     * À utiliser sur TOUTE variable affichée dans une vue.
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    // ── Middleware de contrôle d'accès ────────────────────────────

    /**
     * [MIDDLEWARE] Redirige vers /login si l'utilisateur n'est pas connecté.
     * Stocke l'URL demandée pour redirection post-login.
     * Validation stricte : seules les URLs locales (chemin absolu sans hôte) sont mémorisées.
     */
    public static function requireLogin(): void
    {
        if (!Session::isLoggedIn()) {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            // N'accepter que les chemins locaux : /foo/bar — jamais //evil.com ou ?x=y seul
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

        // Vérifie que le compte est toujours actif (protection contre désactivation admin)
        if (Session::get('user_status') === 'inactive') {
            Session::destroy();
            Session::flash('error', 'Votre compte a été désactivé.');
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }

    /**
     * [MIDDLEWARE] Redirige vers le dashboard si l'utilisateur est déjà connecté.
     * Utilisé sur les pages login et register.
     */
    public static function requireGuest(): void
    {
        if (Session::isLoggedIn()) {
            header('Location: ' . APP_URL . '/dashboard/' . Session::userRole());
            exit;
        }
    }

    /**
     * [MIDDLEWARE] Vérifie que l'utilisateur connecté possède l'un des rôles attendus.
     * Affiche une page 403 sinon.
     *
     * Exemple : Security::requireRole('owner', 'admin')
     */
    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();

        if (!in_array(Session::userRole(), $roles, true)) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
    }

    /**
     * [MIDDLEWARE] Vérifie que l'ID de session appartient bien à l'utilisateur
     * attendu. Protège contre la manipulation directe de l'URL.
     *
     * Exemple : Security::requireOwnership($reservation['client_id'])
     */
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

    // ── Rate limiting ─────────────────────────────────────────────

    /**
     * Protège un point d'entrée contre le brute-force.
     * Stocker les tentatives en session (adapté à une app mono-utilisateur / container).
     *
     * @param string $key          Identifiant de la ressource protégée (ex: 'login:user@example.com')
     * @param int    $maxAttempts  Nombre maximum de tentatives autorisées dans la fenêtre
     * @param int    $decaySeconds Durée (secondes) de la fenêtre glissante (défaut : 5 min)
     */
    public static function checkRateLimit(
        string $key,
        int    $maxAttempts  = 5,
        int    $decaySeconds = 300
    ): void {
        $sessionKey = '_rl_' . hash('sha256', $key);
        $data       = $_SESSION[$sessionKey] ?? ['count' => 0, 'first' => time()];

        // Réinitialise la fenêtre si elle est expirée
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

    /**
     * Réinitialise le compteur d'un rate limit (à appeler après succès).
     */
    public static function clearRateLimit(string $key): void
    {
        unset($_SESSION['_rl_' . hash('sha256', $key)]);
    }

    // ── Validation URL locale ─────────────────────────────────────

    /**
     * Vérifie qu'une URL de redirection est locale (chemin absolu sans hôte).
     * Protège contre les open redirects.
     *
     * @param string $url      URL à valider
     * @param string $fallback URL de secours si invalide
     */
    public static function safeRedirect(string $url, string $fallback): string
    {
        // Accepte uniquement /chemin ou /chemin?query — jamais //host ou https://...
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return $url;
        }
        return $fallback;
    }
}
