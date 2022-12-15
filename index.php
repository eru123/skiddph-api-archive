<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

Bootstrapper::init(__DIR__);

echo "START", PHP_EOL;

$orm = Auth::db();

$orm->table('auth_users')
    ->where([
        'id' => [
            'gte' => 1,
            'lte' => 10,
        ],
    ])
    ->data([[
        'user' => 'admin',
    ]])
    ->update();

echo PHP_EOL, "END", PHP_EOL;
