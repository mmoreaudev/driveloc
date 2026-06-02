<?php
declare(strict_types=1);

$envFile = __DIR__ . '/.env';
if (is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (is_array($lines)) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if ($name === '' || getenv($name) !== false) {
                continue;
            }

            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"'))
                || (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

$displayErrorsFlag = strtolower((string) (getenv('DEBUG_DISPLAY_ERRORS') ?: 'false'));
$displayErrors = in_array($displayErrorsFlag, ['1', 'true', 'yes', 'on'], true);
ini_set('display_errors', $displayErrors ? '1' : '0');
ini_set('display_startup_errors', $displayErrors ? '1' : '0');
error_reporting(E_ALL);

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
if ($requestPath === '/health') {
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'ok';
    exit;
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Session.php';
require_once __DIR__ . '/core/Model.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Router.php';

Session::start();

$router = new Router();

require ROOT_PATH . '/routes/web.php';

$router->dispatch();
