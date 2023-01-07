<?php

use SkiddPH\Core\Config;
use SkiddPH\Core\Plugin\Config as PluginConfig;
/**
 * Access Global Config
 * @param array $args
 * @return mixed
 */
function config(...$args)
{
    if (Config::isSystemConfig((string) @$args[0])) {
        throw new Exception("Cannot access system config from here");
    }

    if (isset($args[1])) {
        Config::set((string) $args[0], $args[1]);
        return;
    }

    return Config::get((string) @$args[0], @$args[2]);
}

/**
 * Access Plugin Config
 * @param   string  $key    The key of the plugin
 * @param   string  $args   The arguments
 * @return mixed
 */
function pconfig($key, ...$args)
{
    $plugin = new PluginConfig($key);
    if (isset($args[1])) {
        $plugin->set((string) $args[0], $args[1]);
        return;
    }

    return $plugin->get((string) @$args[0], @$args[2]);
}