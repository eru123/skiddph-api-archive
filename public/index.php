<?php

require_once __DIR__ . '/../vendor/autoload.php';

use eru123\router\Router;
use eru123\router\Builtin;

$api = require __DIR__ . '/../api/index.php';

$main = new Router();
$main->debug();
$main->bootstrap([
    [Builtin::class, 'remove_header_ads']
]);

$main->fallback('/', function () {
    return 'Hello World';
});

$main->child($api);

$main->run();