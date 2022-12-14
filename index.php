<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use Api\Lib\Date;

echo "START", PHP_EOL;
echo Date::parse("1day ago", "date"), PHP_EOL;
echo Date::parse("now before 1 day", "date"), PHP_EOL;
echo Date::parse("1day ago + 24 hr", "date", Date::UNIT), PHP_EOL;
echo Date::parse("now", "datetime", Date::UNIT), PHP_EOL;
echo Date::parse("now", "Y-m-d h:i A", Date::FORMAT), PHP_EOL;
echo Date::parse("now after 1 day", "date"), PHP_EOL;
echo Date::parse("now + 1 day - (1min * 60 * 24) + (24 * (10 minutes * 6))", "date"), PHP_EOL;
echo Date::parse("now + 1 day - (1min * 60 * 24) + (24 * (10 minutes * 6))", "date"), PHP_EOL;
echo PHP_EOL, "END", PHP_EOL;
