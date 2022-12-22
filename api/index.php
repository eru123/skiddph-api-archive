<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';
Bootstrapper::init(__DIR__ . '/..');

$v1 = require(__DIR__ . '/v1/index.php');

$router = new Router();
$router->base('/api');

$router->add($v1);
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';
Bootstrapper::init(__DIR__ . '/..');

$v1 = require(__DIR__ . '/v1/index.php');

$router = new Router();
$router->base('/api');

$router->add($v1);

$router->run();