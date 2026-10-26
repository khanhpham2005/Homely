<?php
declare(strict_types=1);

ini_set('default_charset', 'UTF-8');
header('Content-Type: text/html; charset=UTF-8');
session_start();

define('APP_NAME', 'Homely');
define('BASE_URL', '/ltw');
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'homely');
define('DB_USER', 'root');
define('DB_PASS', '');

date_default_timezone_set('Asia/Ho_Chi_Minh');
