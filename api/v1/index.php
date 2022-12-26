<?php

$auth = require(__DIR__ . '/auth.php');
$url = require(__DIR__ . '/url.php');

$router = new Router();
return $router->base('/v1')
    ->add($auth)
    ->add($url);
