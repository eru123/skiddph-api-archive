<?php

use Api\Auth\Controller;

$router = new Router();
$router->base('/auth');

$router->post('/signin', [Controller::class, 'signin']);
$router->post('/signup', [Controller::class, 'signup']);
$router->post('/verify/resend/email', [Controller::class, 'resendEmail']);
$router->post('/verify/email/{verifyId}', [Controller::class, 'verifyEmail']);
$router->post('/user/add/email', [Controller::class, 'addEmail']);
$router->post('/user/remove/email', [Controller::class, 'removeEmail']);

return $router;
