<?php

require_once __DIR__ . '/vendor/autoload.php';

use SkiddPH\Core\Bootstrapper;
use SkiddPH\Model\User;
use SkiddPH\Plugin\DB\DB;

Bootstrapper::init(__DIR__);

$user = User::first('user', 'admin');
if ($user) {
    echo json_encode($user->array(), JSON_PRETTY_PRINT) . PHP_EOL;
    $user->delete();
}

$user = User::create();

$user->user = 'admin';
$user->hash = 'pass';
$user->created_at = DB::raw('NOW()');
$user->updated_at = DB::raw('NOW()');
$user->save();

sleep(5);

$alt = User::first('user', 'admin123');
if ($alt) {
    $alt->delete();
}

$user->user = 'admin123';
$user->updated_at = DB::raw('NOW()');
$user->update();