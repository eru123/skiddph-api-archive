<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

echo "<pre>";

\Api\Core\Bootstrapper::init(__DIR__);
// $orm = \Api\Core\Database::connect('default');

echo "OK";
echo "</pre>";