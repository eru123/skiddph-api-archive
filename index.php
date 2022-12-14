<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

Bootstrapper::init(__DIR__);

use Api\Lib\Date;
use Api\Auth\JWT;

echo "START", PHP_EOL;
$cfg = Auth::config();
// echo print_r($cfg->all(), true), PHP_EOL;
$token = JWT::encode([
    'user_id' => 1,
    'iat' => "now",
    'exp' => "now + 1 day",
    'nbf' => "now + 1 hour",
]);
echo $token, PHP_EOL;
echo print_r(JWT::decode($token), true), PHP_EOL;
// echo Date::parse("1day ago", "date"), PHP_EOL;
// echo Date::parse("now before 1 day", "date"), PHP_EOL;
// echo Date::parse("1day ago + 24 hr", "date", Date::UNIT), PHP_EOL;
// echo Date::parse("now", "datetime", Date::UNIT), PHP_EOL;
// echo Date::parse("now", "Y-m-d h:i A", Date::FORMAT), PHP_EOL;
// echo Date::parse("now after 1 day", "date"), PHP_EOL;
// echo Date::parse("now + 1 day - (1min * 60 * 24) + (24 * (10 minutes * 6))", "date"), PHP_EOL;
// echo Date::parse("now + 1 day - (1min * 60 * 24) + (24 * (10 minutes * 6))", "date"), PHP_EOL;
echo PHP_EOL, "END", PHP_EOL;
