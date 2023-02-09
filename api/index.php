<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';

use SkiddPH\Core\Bootstrapper;
use SkiddPH\Core\HTTP\Request;
use eru123\Router\Router;

Bootstrapper::init(__DIR__ . '/..');

if (e('ENV') === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

Request::allowCORS();

$drc = require(__DIR__ . '/drc.php');
$v1 = require(__DIR__ . '/v1.php');

$router = new Router();
$router->base('/api');
$router->exception($drc);
$router->error($drc);

$router->add($v1);
$router->run();