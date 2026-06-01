<?php
declare(strict_types=1);

// ── Application ──────────────────────────────────────────────────────────────
define('APP_NAME', 'DriveLoc');

// APP_URL : résolution par priorité
//   1. Variable APP_URL définie manuellement (.env, hébergeur)
//   2. RENDER_EXTERNAL_URL injecté par Render
//   3. Domaine courant HTTP(S)
//   4. Fallback développement local Docker
$_appUrl = getenv('APP_URL');
if (!$_appUrl) {
    $_renderUrl = getenv('RENDER_EXTERNAL_URL');
    if ($_renderUrl) {
        $_appUrl = $_renderUrl;
    }

    if (!$_appUrl && !empty($_SERVER['HTTP_HOST'])) {
        $_scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $_appUrl = $_scheme . '://' . $_SERVER['HTTP_HOST'];
        unset($_scheme);
    }

    if (!$_appUrl) {
        $_appUrl = 'http://localhost:8080';
    }

    unset($_renderUrl);
}
define('APP_URL', rtrim($_appUrl, '/'));
unset($_appUrl);

// ── Chemins absolus ──────────────────────────────────────────────────────────
define('ROOT_PATH',   __DIR__ . '/..');
define('VIEWS_PATH',  ROOT_PATH . '/views');

// ── Upload ───────────────────────────────────────────────────────────────────
define('UPLOAD_PATH', ROOT_PATH . '/uploads/vehicles/');
define('UPLOAD_URL',  APP_URL   . '/uploads/vehicles/');
define('MAX_FILE_SIZE',     5 * 1024 * 1024);           // 5 Mo
define('ALLOWED_IMG_EXT',  ['jpg', 'jpeg', 'png', 'webp']);
define('ALLOWED_IMG_MIME', ['image/jpeg', 'image/png', 'image/webp']);
