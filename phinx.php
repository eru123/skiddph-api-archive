<?php

require __DIR__ . '/vendor/autoload.php';
\Api\Core\Bootstrapper::init(__DIR__);

$plugin_config = (new \Api\Core\PluginConfig('DATABASES'))->all();
$config_envs = \Api\Database\Helper::toPhinxConfig($plugin_config);

$config = [
    'paths' => [
        'migrations' => __DIR__.'/db/migrations',
        'seeds' => __DIR__.'/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog'
    ],
    'version_order' => 'creation'
];

$config['environments'] = array_merge($config['environments'], $config_envs);
return $config;
