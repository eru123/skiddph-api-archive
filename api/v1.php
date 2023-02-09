<?php

use eru123\Router\Router;
use SkiddPH\Controller\Auth;
use SkiddPH\Plugin\FileUploader\Controller as FileUploader;

// AUTH PLUGIN
$auth = new Router();
$auth->base('/auth');
$auth->post('/signin', [Auth::class, 'signin']);
$auth->post('/signup', [Auth::class, 'signup']);
$auth->post('/email/send', [Auth::class, 'emailSend']);
$auth->post('/email/verify', [Auth::class, 'emailVerify']);
$auth->get('/email/verify/{emailToken}', [Auth::class, 'emailVerify']);
$auth->post('/email/add', [Auth::class, 'addEmail']);
$auth->post('/email/remove', [Auth::class, 'removeEmail']);

$auth->post('/user/update/username', [Auth::class, 'updateUsername']);
$auth->post('/user/update/password', [Auth::class, 'updatePassword']);
// $auth->post('/user/{userId}/add/role', [Auth::class, 'addRole']);
// $auth->post('/user/{userId}/remove/role', [Auth::class, 'removeRole']);

// $auth->get('/users', [Auth::class, 'users']);
$auth->get('/user', [Auth::class, 'user']);
$auth->get('/user/{userId}', [Auth::class, 'user']);

// FILEUPLOADER PLUGIN
$fileUploader = new Router();
$fileUploader->base('/fileuploader');
$fileUploader->post('/upload', [FileUploader::class, 'upload']);
$fileUploader->post('/files', [FileUploader::class, 'files']);
$fileUploader->get('/stream/{id}', [FileUploader::class, 'stream']);
$fileUploader->get('/download/{id}', [FileUploader::class, 'download']);

$router = new Router();
$router->base('/v1');
$router->add($auth);
$router->add($fileUploader);

return $router;