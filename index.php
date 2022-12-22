<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

Bootstrapper::init(__DIR__);

echo "START", PHP_EOL;

// $data = Auth::register('admin4', 'pass', 'superadmin,admin, moderator', [
//     'email' => [
//         'admin4@localhost',
//         'admin4@xyz'
//     ],
//     'name' => 'Admin4',
//     'surname' => 'Admin4',
//     'phone' => '2123456789',
//     'profile' => [
//         'name' => 'Admin4',
//         'surname' => 'Admin4',
//         'phone' => '2123456789',
//     ]
// ]);

$data = Auth::login('admin4', 'pass');
// $refresh = Auth::refreshToken($data['token'], $data['refresh_token']);

echo json_encode($data, JSON_PRETTY_PRINT), PHP_EOL;
// echo json_encode($refresh, JSON_PRETTY_PRINT), PHP_EOL;

echo PHP_EOL, "END", PHP_EOL;