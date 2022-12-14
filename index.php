<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use Api\Lib\Date;

// echo "<pre>";
echo "START", PHP_EOL;

// Bootstrapper::init(__DIR__);
// $orm = Database::connect('default');

// // print_r($orm->table('auth_users'));
// echo $orm->table('auth_users')
//     ->insert([
//         [
//             'user' => 'admin1',
//             'hash' => password_hash('pass', PASSWORD_BCRYPT, ['cost' => 12]),
//             'created_at' => $orm->f("NOW()"),
//         ]
//     ])->rowCount(), PHP_EOL;

// // echo last inserted id
// echo $orm->lastInsertId(), PHP_EOL;

echo Date::parse("1d", "h"), PHP_EOL;
echo Date::parse("1d + 1h", "h"), PHP_EOL;
echo Date::parse("1h - 1min", "m"), PHP_EOL;
echo Date::parse("1minute + 0mins", "s"), PHP_EOL;
echo Date::parse("1minute + 0mins", "ms"), PHP_EOL;
echo Date::parse("now", "datetime"), PHP_EOL;
echo Date::parse("time", "date"), PHP_EOL;
echo Date::parse("now", "time"), PHP_EOL;
echo Date::parse("now", "timestamp"), PHP_EOL;
// echo Date::parse("now", "h:i:s A", Date::FORMAT), PHP_EOL;
// echo Date::parse("now + 1h", "h:i:s A", Date::FORMAT), PHP_EOL;
// echo Date::parse("2000-01-01 00:00:00 + 1d - 1s", "Y-m-d H:i:s", Date::FORMAT), PHP_EOL;

echo PHP_EOL, "END", PHP_EOL;
// echo "</pre>";
