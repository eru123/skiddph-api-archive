<?php

require_once __DIR__ . '/vendor/autoload.php';

use eru123\orm\ORM;
use SkiddPH\Core\Bootstrapper;

Bootstrapper::init(__DIR__);

print_r(ORM::createFromComposer([ 'SkiddPH', 'eru123' ]));