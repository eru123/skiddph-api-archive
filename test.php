<?php

require_once __DIR__ . '/vendor/autoload.php';

use SkiddPH\Core\Bootstrapper;
use SkiddPH\Model\User;
use SkiddPH\Plugin\DB\DB;

Bootstrapper::init(__DIR__);

DB::query('SELECT * FROM auth_users WHERE ?', [1], null, $pdo);

var_dump($pdo);
