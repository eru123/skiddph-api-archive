<?php

use eru123\router\Router;

$v1 = require __DIR__ . '/v1/index.php';

$api = new Router();
$api->debug();
$api->base('/api');

$api->bootstrap([

]);

$api->fallback('/', function () {
    return [
        'error' => 'Route Not found',
    ];
});

$api->child($v1);

return $api;