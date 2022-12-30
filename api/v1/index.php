<?php

$auth = require(__DIR__ . '/auth.php');
$url = require(__DIR__ . '/url.php');
$fileuploader = require(__DIR__ . '/fileuploader.php');

$router = new Router();
return $router->base('/v1')
    ->add($auth)
    ->add($url)
    ->add($fileuploader);
