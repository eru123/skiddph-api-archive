<?php

use Plugin\FileUploader\Controller;

$router = new Router();
$router->base('/fileuploader');

// No Roles
$router->post('/upload', [Controller::class, 'upload']);

// Public Access
$router->get('/stream/{id}', [Controller::class, 'stream']);
$router->get('/download/{id}', [Controller::class, 'download']);

return $router;
