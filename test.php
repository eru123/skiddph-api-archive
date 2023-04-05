<?php

require_once __DIR__ . '/vendor/autoload.php';

use eru123\orm\ORM;
use SkiddPH\Core\Bootstrapper;

Bootstrapper::init(__DIR__);

$orm = ORM::createFromComposer([ 'SkiddPH', 'eru123' ], __DIR__);

// $orm->EmailAttachments()->insert([
//     'email_id' => 1,
//     'path' => 'test.txt'
// ]);

$orm->EmailLogs()->insert([
    'email_id' => 1,
    'message' => 'sent',
    'created_at' => date('Y-m-d H:i:s')
]);