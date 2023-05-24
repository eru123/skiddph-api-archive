<?php

require_once __DIR__ . '/vendor/autoload.php';

use eru123\orm\Raw;


echo Raw::build("SELECT ? FROM ? WHERE `id` IN ?", [
    Raw::columns([
        'total' => 'COUNT(`total`)',
    ]),
    Raw::table('users', 'u'),
    Raw::in(["Hello", '"World"', 'Hello\'s World']),
]), PHP_EOL;

// use eru123\orm\ORM;
// use SkiddPH\Core\Bootstrapper;

// Bootstrapper::init(__DIR__);

// $orm = ORM::createFromComposer([ 'SkiddPH', 'eru123' ], __DIR__);

// $orm->EmailAttachments()->insert([
//     'email_id' => 1,
//     'path' => 'test.txt'
// ]);

// $orm->EmailLogs()->insert([
//     'email_id' => 1,
//     'message' => 'sent',
//     'created_at' => date('Y-m-d H:i:s')
// ]);

// $orm->EmailQueues()->insert([
//     'to' => 'hello@jericho.work',   
//     'cc' => 'jericho@skiddph.com',
//     'bcc' => null,
//     'subject' => 'Test',
//     'body' => 'Test',
//     'priority' => 1,
//     'for_approval' => false,
//     'approved_at' => null,
//     'pending_at' => null,
//     'delivered_at' => null,
//     'failed_at' => null,
//     'created_at' => date('Y-m-d H:i:s'),
//     'deleted_at' => null
// ]);

// use eru123\str\Sub;


// $str =  '{{fb}} Please use my fb account and my ig account {{ig}} or email me at {{email}}.';
// $vars = [
//     'fb' => 'https://web.facebook.com/lighty262',
//     'ig' => 'http://localhost.com/',
//     'email' => 'yeoligoakino@gmail.com'
// ];

// $default_properties = [
    // 'target' => '_blank',
    // 'rel' => 'noopener noreferrer'
    // should have referrer policy
// ];

// $str = Sub::var_double_curly_brace($str, $vars, 'domain.com');
// $str = Sub::html_anchor($str, $default_properties);
// $str = Sub::html_email($str, $default_properties);

// echo $str;

