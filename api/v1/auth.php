<?php

use Api\Auth\Controller;

$router = new Router();
$router->base('/auth');

// No Roles
$router->post('/signin', [Controller::class, 'signin']);
$router->post('/signup', [Controller::class, 'signup']);
$router->post('/verify/resend/email', [Controller::class, 'resendEmail']);
$router->post('/verify/email/{verifyId}', [Controller::class, 'verifyEmail']);
$router->post('/user/add/email', [Controller::class, 'addEmail']);
$router->post('/user/remove/email', [Controller::class, 'removeEmail']);
$router->post('/user/change/user', [Controller::class, 'changeUsername']);
$router->post('/user/change/password', [Controller::class, 'changePassword']);

// ASSIGNROLE
$router->post('/user/{userId}/add/role', [Controller::class, 'addRole']);
$router->post('/user/{userId}/remove/role', [Controller::class, 'removeRole']);

return $router;
