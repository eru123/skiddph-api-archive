<?php

use Plugin\URL\Controller;

$router = new Router();
$router->base('/url');

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

$router->post('/signup', [Controller::class, 'signup']);

return $router;
