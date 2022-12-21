<?php

use Api\Auth\Model\Info;
use Api\Auth\User;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

Bootstrapper::init(__DIR__);

echo "START", PHP_EOL;

$data = Auth::login('admin', 'pass');
$refresh = Auth::refreshToken($data['token'], $data['refresh_token']);

echo json_encode($data, JSON_PRETTY_PRINT), PHP_EOL;
echo json_encode($refresh, JSON_PRETTY_PRINT), PHP_EOL;

echo PHP_EOL, "END", PHP_EOL;