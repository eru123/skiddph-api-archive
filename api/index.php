<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';

use SkiddPH\Core\Bootstrapper;
use SkiddPH\Core\HTTP\Request;
use SkiddPH\Core\HTTP\Router;

// Init the bootstrapper
// This will load the env file and the config file
Bootstrapper::init(__DIR__ . '/..');

if (config('env.ENVIRONMENT') === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Allow CORS
Request::allowCORS();

/** 
 * API Version 1 Router
 * @var Router $v1 
 */
$v1 = require(__DIR__ . '/v1.php');

/**
 * Main API Router 
 * @var Router $router 
 */
$router = new Router();

// Base API
$router->base('/api');

// Add API Child Routers
$router->add($v1);

// Run the router
$router->run();