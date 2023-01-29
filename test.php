<?php

require_once __DIR__ . '/vendor/autoload.php';

use SkiddPH\Core\Bootstrapper;
use SkiddPH\Helper\Date;
use SkiddPH\Plugin\SMTP\SMTP;

Bootstrapper::init(__DIR__);

$smtp = SMTP::use();
$smtp->to('yeoligoakino@gmail.com');
$smtp->subject('DateTime: '.Date::parse('now', 'datetime'));
$smtp->text('Test');
$smtp->html();
$smtp->send();