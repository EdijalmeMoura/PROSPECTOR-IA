<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Compatibilidade com PHP 8.5: o Laravel 12 ainda referencia constantes PDO
// deprecadas (MYSQL_ATTR_SSL_CA) ao carregar o config padrao do framework.
// Em PHP < 8.5 nada muda; em 8.5 apenas E_DEPRECATED deixa de vazar no HTML.
if (PHP_VERSION_ID >= 80500) {
    error_reporting(error_reporting() & ~E_DEPRECATED);
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
