<?php
declare(strict_types=1);

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
if ($requestPath === '/health') {
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'ok';
    exit;
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Session.php';
require_once __DIR__ . '/core/Security.php';
require_once __DIR__ . '/core/Model.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/AutoInstaller.php';

if (!AutoInstaller::isEnabled()) {
    try {
        if (AutoInstaller::hasMissingRequiredTables()) {
            http_response_code(503);
            header('Content-Type: text/plain; charset=UTF-8');
            echo "Base de donnees non initialisee. Activez AUTO_INSTALL_DB=true temporairement, redeployez, ouvrez / puis remettez AUTO_INSTALL_DB=false.";
            exit;
        }
    } catch (Throwable $e) {
        error_log('DB bootstrap check failed: ' . $e->getMessage());
    }
}

if (AutoInstaller::isEnabled()) {
    try {
        AutoInstaller::ensureDatabaseInitialized();
    } catch (Throwable $e) {
        error_log('AutoInstaller failed: ' . $e->getMessage());
        http_response_code(500);
        echo 'Erreur d\'initialisation de la base de donnees.';
        exit;
    }
}

Session::start();

// ── HTTP Security Headers ─────────────────────────────────────────────────────
// Empêche le navigateur de deviner le Content-Type (protection MIME sniffing)
header('X-Content-Type-Options: nosniff');
// Empêche l'intégration dans un <iframe> (protection clickjacking)
header('X-Frame-Options: SAMEORIGIN');
// Limite la fuite d'informations de referer vers des tiers
header('Referrer-Policy: strict-origin-when-cross-origin');
// Désactive les fonctionnalités navigateur non utilisées
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
// Content Security Policy — restreint les sources autorisées
// 'unsafe-inline' nécessaire pour les <script> et <style> inline des vues Bootstrap
header(
    "Content-Security-Policy: "
    . "default-src 'self'; "
    . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "
    . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "
    . "img-src 'self' data: blob:; "
    . "font-src 'self' https://cdn.jsdelivr.net; "
    . "connect-src 'self'; "
    . "frame-ancestors 'none';"
);
// ─────────────────────────────────────────────────────────────────────────────

$router = new Router();

require ROOT_PATH . '/routes/web.php';

$router->dispatch();
