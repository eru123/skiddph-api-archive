<?php

require_once __DIR__ . '/vendor/autoload.php';

use eru123\orm\ORM;
use SkiddPH\Core\Bootstrapper;

Bootstrapper::init(__DIR__);

$orm = ORM::createFromComposer([ 'SkiddPH', 'eru123' ], __DIR__);

$orm->EmailAttachments()->insert([
    'email_id' => 1,
    'path' => 'test.txt'
]);

$orm->EmailLogs()->insert([
    'email_id' => 1,
    'message' => 'sent',
    'created_at' => date('Y-m-d H:i:s')
]);

$orm->EmailQueues()->insert([
    'to' => 'hello@jericho.work',   
    'cc' => 'jericho@skiddph.com',
    'bcc' => null,
    'subject' => 'Test',
    'body' => 'Test',
    'priority' => 1,
    'for_approval' => false,
    'approved_at' => null,
    'pending_at' => null,
    'delivered_at' => null,
    'failed_at' => null,
    'created_at' => date('Y-m-d H:i:s'),
    'deleted_at' => null
]);