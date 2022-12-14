<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use Api\Lib\Date;

echo "START", PHP_EOL;
echo Date::parse("1day ago", "date"), PHP_EOL;
echo Date::parse("now before 1 day", "date"), PHP_EOL;
echo Date::parse("now", "date"), PHP_EOL;
echo Date::parse("now after 1 day", "date"), PHP_EOL;
echo PHP_EOL, "END", PHP_EOL;
