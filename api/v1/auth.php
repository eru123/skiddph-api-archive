<?php

use eru123\router\Router;
use SkiddPH\Controller\Auth;

$auth = new Router();
$auth->base('/auth');
// $auth->post('/signin', [Auth::class, 'signin']);
// $auth->post('/signup', [Auth::class, 'signup']);
// $auth->post('/email/send', [Auth::class, 'emailSend']);
// $auth->post('/email/verify', [Auth::class, 'emailVerify']);
// $auth->get('/email/verify/{emailToken}', [Auth::class, 'emailVerify']);
// $auth->post('/email/add', [Auth::class, 'addEmail']);
// $auth->post('/email/remove', [Auth::class, 'removeEmail']);

return $auth;