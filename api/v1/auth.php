<?php

$router = new Router();
$router->base('/auth');

$router->get('/user/{id}/edit', function ($params) {
    
});

return $router;