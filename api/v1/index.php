<?php

$auth = require(__DIR__ . '/auth.php');

$router = new Router();
$router->base('/v1')
    ->add($auth);

return $router;
