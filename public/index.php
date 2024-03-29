<?php

require_once __DIR__ . '/../vendor/autoload.php';

use SkiddPH\Core\Bootstrapper;
use eru123\router\Router;
use eru123\router\Builtin;

Bootstrapper::init(__DIR__ . '/..');

$api = require __DIR__ . '/../api/index.php';

$main = new Router();
$main->bootstrap([
    [Builtin::class, 'remove_header_ads'],
]);

$main->get('/test', function() {
    require __DIR__ . '/../test.php';
});

// vite($main);

$main->child($api);
$main->run();