<?php

$auth = require(__DIR__ . '/auth.php');

$router = new Router();
$router->base('/v1');

$router->add($auth);

return $router;