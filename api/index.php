<?php

$v1 = require(__DIR__ . '/v1/index.php');

$router = new Router();
$router->base('/api');

$router->add($v1);

return $router;
