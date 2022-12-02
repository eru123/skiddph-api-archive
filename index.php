<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Lib\URL;

Dotenv::createImmutable(__DIR__)->load();

echo "<pre>";

$url = new URL("https://domain.com/asdasd?a=1&b=2#sdfsd");
echo $url->get(), PHP_EOL;
print_r($url->extract());
echo $url->addQuery(['a' => 4, 'b' => 5])->get();
echo "</pre>";