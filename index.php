<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

// echo "<pre>";
echo "START", PHP_EOL;

\Api\Core\Bootstrapper::init(__DIR__);
$orm = \Api\Database\Database::connect('default');

// print_r($orm->table('auth_users'));
echo $orm->table('auth_users')
    ->insert([
        [
            'user' => 'admin4',
            'hash' => password_hash('pass', PASSWORD_BCRYPT, ['cost' => 12]),
            'created_at' => date('Y-m-d H:i:s'),
        ]
    ])->rowCount(), PHP_EOL;

// echo last inserted id
echo $orm->lastInsertId(), PHP_EOL;

echo PHP_EOL, "END";
// echo "</pre>";