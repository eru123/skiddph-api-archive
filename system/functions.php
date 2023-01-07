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
        if (empty($args[0])) {
            throw new Exception("Cannot access root config from here");
        }

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

/**
 * Session helper
 * @param  string  $key         The key of the session
 * @param  mixed   $value       The value of the session
 * @param  mixed   $default     The default value to return if the key is not found.
 * @return mixed
 */
function gsession($key, $value = null, $default = null)
{
    $data = & $_SESSION;
    if (empty($data)) {
        $data = [];
    }

    $keys = explode('.', $key);
    foreach ($keys as $key) {
        if (!isset($data[$key]) || !is_array($data[$key])) {
            $data[$key] = [];
        }

        $data = & $data[$key];
    }

    if (isset($value)) {
        $data = $value;
        return;
    }

    return $data === null ? $default : $data;
}

/**
 * ENV helper
 * @param  string  $key         The key of the env
 * @param  bool|null|string   $default     The default value to return if the key is not found.
 * @return bool|null|string
 */
function env($key, $default = null)
{
    return empty($_ENV[$key]) ? $default : $_ENV[$key];
}