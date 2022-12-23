<?php

$router = new Router();
$router->base('/auth');

$router->get('/user/{id}/edit', function ($p) {
    Auth::accessControl('superadmin,admin');
    return [
        'id' => $p['id'],
        'name' => 'John Doe',
    ];
});

$router->post('/signin', function () {
    $body = Request::bodySchema([
        'user' => [
            'required' => true,
        ],
        'pass' => [
            'required' => true,
        ],
    ]);

    return Auth::login($body['user'], $body['pass']);
});

return $router;