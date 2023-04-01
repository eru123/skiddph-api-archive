<?php

use eru123\router\Router;

$auth = require __DIR__ . '/auth.php';
$webtools = require __DIR__ . '/webtools.php';

$v1 = new Router();
$v1->base('/v1');

$v1->child($auth);
$v1->child($webtools);

return $v1;

// $auth->post('/user/update/username', [Auth::class, 'updateUsername']);
// $auth->post('/user/update/password', [Auth::class, 'updatePassword']);
// $auth->post('/user/{userId}/add/role', [Auth::class, 'addRole']);
// $auth->post('/user/{userId}/remove/role', [Auth::class, 'removeRole']);

// $auth->get('/users', [Auth::class, 'users']);
// $auth->get('/user', [Auth::class, 'user']);
// $auth->get('/user/{userId}', [Auth::class, 'user']);

// FILEUPLOADER PLUGIN
// $fileUploader = new Router();
// $fileUploader->base('/fileuploader');
// $fileUploader->post('/upload', [FileUploader::class, 'upload']);
// $fileUploader->post('/files', [FileUploader::class, 'files']);
// $fileUploader->get('/stream/{id}', [FileUploader::class, 'stream']);
// $fileUploader->get('/download/{id}', [FileUploader::class, 'download']);

// $router = new Router();
// $router->base('/v1');
// $router->add($auth);
// $router->add($fileUploader);

// return $router;