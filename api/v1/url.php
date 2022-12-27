<?php

use Plugin\URL\Controller;

$router = new Router();
$router->base('/url');

$router->post('/signin', [Controller::class, 'signin']);
$router->post('/signup', [Controller::class, 'signup']);
$router->post('/verify/resend/email', [Controller::class, 'resendEmail']);
$router->post('/verify/email/{verifyId}', [Controller::class, 'verifyEmail']);
return $router;
