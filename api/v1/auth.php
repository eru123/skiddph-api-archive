<?php

$router = new Router();
$router->base('/auth');

$router->get('/user/{id}/edit', function ($p) {
    return [
        'id' => $p['id'],
        'name' => 'John Doe',
    ];
});

return $router;
