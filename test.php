<?php

require_once __DIR__ . '/vendor/autoload.php';

use SkiddPH\Plugin\DB\DB;

echo (DB::raw('SELECT * FROM `users` WHERE `id` = ?', [1])), PHP_EOL;
