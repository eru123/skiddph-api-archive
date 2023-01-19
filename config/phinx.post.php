<?php

require_once __DIR__ . '/../vendor/autoload.php';

use SkiddPH\Core\Bootstrapper;
use SkiddPH\Plugin\Database\Database;

$workdir = __DIR__ . '/..';

/**
 * Load the config.php file
 * to get the database config
 */
Bootstrapper::init($workdir);

/** 
 * The Initial and Default Phinx Config
 * @var mixed $config 
 */
$config = [
    'paths' => [
        'migrations' => workdir() . '/db/migrations',
        'seeds' => workdir() . '/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'migration_logs'
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