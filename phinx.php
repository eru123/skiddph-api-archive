<?php

require __DIR__ . '/vendor/autoload.php';

use SkiddPH\Core\Bootstrapper;
use SkiddPH\Plugin\Database\Database;

/**
 * Load the config.php file
 * to get the database config
 */
Bootstrapper::init(__DIR__);

/** 
 * The Initial and Default Phinx Config
 * @var mixed $config 
 */
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

/**
 * Transform the config to Phinx Config 
 * @var mixed $phinx 
 */
$phinx = Database::phinxConfig();

// Merge the config
$config['environments'] = array_merge($config['environments'], $phinx);
return $config;