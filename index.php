<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config;

echo 'Hello World!';
Dotenv::createImmutable(__DIR__)->load();
// Config::set('app', [
//     'name' => $_ENV['APP_NAME'],
//     'url' => $_ENV['APP_URL'],
//     'timezone' => $_ENV['APP_TIMEZONE'],
//     'locale' => $_ENV['APP_LOCALE'],
//     'key' => $_ENV['APP_KEY'],
//     'debug' => $_ENV['APP_DEBUG'],
// ]);