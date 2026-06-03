<?php
declare(strict_types=1);

define('APP_NAME', 'DriveLoc');


$_appUrl = getenv('APP_URL');
define('APP_URL', $_appUrl);
unset($_appUrl);


define('ROOT_PATH',   __DIR__ . '/..');
define('VIEWS_PATH',  ROOT_PATH . '/views');


define('UPLOAD_PATH', ROOT_PATH . '/uploads/vehicles/');
define('UPLOAD_URL',  APP_URL   . '/uploads/vehicles/');
define('MAX_FILE_SIZE',     5 * 1024 * 1024);
define('ALLOWED_IMG_EXT',  ['jpg', 'jpeg', 'png', 'webp']);
define('ALLOWED_IMG_MIME', ['image/jpeg', 'image/png', 'image/webp']);
