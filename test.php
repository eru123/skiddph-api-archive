<?php

require_once __DIR__ . '/vendor/autoload.php';

use SkiddPH\Core\Bootstrapper;
use SkiddPH\Model\User;
use SkiddPH\Plugin\DB\DB;

Bootstrapper::init(__DIR__);

// $user = new User();
var_dump(
    User::where('id', 1)
        ->deleteSql()
);
