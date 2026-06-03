<?php
declare(strict_types=1);

$envFile = __DIR__ . '/.env';


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
