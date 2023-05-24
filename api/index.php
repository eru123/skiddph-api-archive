<?php

use eru123\router\Router;

$api = new Router();
$api->base('/api');
$api->bootstrap([
]);

$api->fallback('/', function () {
    return [
        'error' => 'Route Not found',
    ];
});

return $api;
