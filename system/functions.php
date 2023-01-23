<?php

use SkiddPH\Core\Config;
use SkiddPH\Helper\Date;

/**
 * Access Global Config
 * @param array $args
 * @return mixed
 */
function cfg(...$args)
{
    if (Config::isSystemConfig((string) @$args[0])) {
        throw new Exception("Cannot access system config from here. Use sys() instead.");
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
 * Access System Config
 * @param   string  $key        The key of the config
 * @param   mixed   $value      The value of the config
 * @param   mixed   $default    The default value to return if the key is not found.
 * @return  mixed
 */
function sys($key = null, $value = null, $default = null)
{
    $key = empty($key) ? '' : '.' . $key;
    $key = 'system' . $key;
    if (isset($value)) {
        Config::set($key, $value);
        return;
    }

    return Config::get($key, $default);
}

/**
 * Access Read-only Plugin Config
 * @param   string  $key        The key of the config
 * @param   mixed   $default    The default value to return if the key is not found.
 * @return  mixed
 */
function pcfg($key = '', $default = null)
{
    $has_key = !empty($key);
    $key = 'plugins' . ($has_key ? '.' . $key : '');
    return sys($key, null, $default);
}

/**
 * Session helper
 * @param  string  $key         The key of the session
 * @param  mixed   $value       The value of the session
 * @param  mixed   $default     The default value to return if the key is not found.
 * @return mixed
 */
function sess($key, $value = null, $default = null)
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
function e($key, $default = null)
{
    return empty($_ENV[$key]) ? $default : $_ENV[$key];
}

/**
 * Get workdir path
 * @return string
 */
function workdir()
{
    return sys('workdir');
}

/**
 * Get configdir path
 * @return string
 */
function configdir()
{
    return sys('configdir');
}

/**
 * Date time initializer
 * @return void
 */
function datetime_init()
{
    // Set Timezone
    $tz = sys('timezone', sys('plugins.app.timezone', date_default_timezone_get()));
    date_default_timezone_set($tz);

    // Set Date and Time
    sys('ms', microtime(true));
    sys('time', floor(sys('ms')));
    sys('date', date('Y-m-d H:i:s', sys('time')));
    Date::setTime(sys('time'));
}