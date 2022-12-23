<?php

require __DIR__ . '/vendor/autoload.php';

Bootstrapper::init(__DIR__);

$config = [
    'paths' => [
        'migrations' => __DIR__ . '/db/migrations',
        'seeds' => __DIR__ . '/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog'
    ],
    'version_order' => 'creation'
];

$config['environments'] = array_merge($config['environments'], Database::phinxConfig());
return $config;