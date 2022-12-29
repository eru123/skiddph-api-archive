<?php

use Plugin\FileUploader\Controller;

$router = new Router();
$router->base('/fileuploader');

// No Roles
$router->post('/upload', [Controller::class, 'upload']);

return $router;
