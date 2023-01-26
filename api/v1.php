<?php

use SkiddPH\Core\HTTP\Router;
use SkiddPH\Plugin\Auth\Controller as Auth;
use SkiddPH\Controller\Auth as AuthController;
use SkiddPH\Plugin\FileUploader\Controller as FileUploader;

// AUTH PLUGIN
$auth = new Router();
$auth->base('/auth');
$auth->post('/signin', [AuthController::class, 'signin']);
$auth->post('/signup', [AuthController::class, 'signup']);
$auth->post('/verify/resend/email', [Auth::class, 'resendEmail']);
$auth->post('/verify/email/{verifyId}', [Auth::class, 'verifyEmail']);
$auth->post('/user/add/email', [Auth::class, 'addEmail']);
$auth->post('/user/remove/email', [Auth::class, 'removeEmail']);
$auth->post('/user/change/user', [Auth::class, 'changeUsername']);
$auth->post('/user/change/password', [Auth::class, 'changePassword']);
$auth->post('/user/{userId}/add/role', [Auth::class, 'addRole']);
$auth->post('/user/{userId}/remove/role', [Auth::class, 'removeRole']);
$auth->get('/user', [Auth::class, 'getUser']);
$auth->get('/user/{userId}', [Auth::class, 'getUser']);

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
