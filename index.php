<?php
declare(strict_types=1);

$envFile = __DIR__ . '/.env';

$displayErrorsFlag = strtolower((string) (getenv('DEBUG_DISPLAY_ERRORS') ?: 'false'));
$displayErrors = in_array($displayErrorsFlag, ['1', 'true', 'yes', 'on'], true);
ini_set('display_errors', $displayErrors ? '1' : '0');
ini_set('display_startup_errors', $displayErrors ? '1' : '0');
error_reporting(E_ALL);


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
