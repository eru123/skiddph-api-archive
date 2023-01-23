<?php

require_once __DIR__ . '/../vendor/autoload.php';

$workdir = __DIR__ . '/..';

/**
 * Load the config.php file
 * to get the database config
 */
\SkiddPH\Core\Bootstrapper::init($workdir);

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
$phinx = \SkiddPH\Plugin\DB\DB::phinxConfig();

// Merge the config
$config['environments'] = array_merge($config['environments'], $phinx);
return $config;