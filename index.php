<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/vendor/autoload.php';
Bootstrapper::init(__DIR__);

$api = require __DIR__ . '/api/index.php';

$router = new Router();
$router->base('/');
$router->add($api);
$router->run();
