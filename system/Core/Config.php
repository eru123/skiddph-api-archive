<?php

namespace SkiddPH\Core;

class Config
{
    /**
     * Holds the static key-value pairs of the configuration.
     * @var     array
     */
    private static $data = [];

    /**
     * Check if a configuration key exists.
     * @param   string  $key    The key of the configuration.
     * @return  bool   `true` if the key exists, `false` otherwise.
     */
    final public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    /**
     * Clear the entire configuration array.
     * @return  void
     */
    final public static function clear(): void
    {
        self::$data = [];
    }

    /**
     * Set a configuration value.
     * @param   string  $key    The key of the configuration.
     * @param   mixed   $value  The value of the configuration.
     * @return  void
     */
    final public static function set($key = null, $value = null)
    {
        if (empty($key)) {
            self::$data = $value;
            return;
        }

        $keys = explode('.', $key);
        if (count($keys) === 1) {
            self::$data[$key] = $value;
            return;
        }

        $data = & self::$data;
        if (empty($data)) {
            $data = [];
        }

        foreach ($keys as $key) {
            if (!isset($data[$key]) || !is_array($data[$key])) {
                $data[$key] = [];
            }

            $data = & $data[$key];
        }

        $data = $value;
    }

    /**
     * Get a configuration value.
     * @param   string  $key    The key of the configuration.
     * @param   mixed   $default    The default value to return if the key is not found.
     * @return  mixed  The value of the configuration.
     */
    final public static function get($key = null, $default = null)
    {
        if (empty($key)) {
            return self::$data;
        }

        $keys = explode('.', $key);
        if (count($keys) === 1) {
            return self::$data[$key] ?? $default;
        }

        $data = self::$data;
        foreach ($keys as $key) {
            if (!isset($data[$key])) {
                return $default;
            }

            $data = $data[$key];
        }

        return $data;
    }

    /**
     * Remove a configuration key.
     * @param   string  $key    The key of the configuration.
     * @return  void
     */
    final public static function delete($key)
    {
        if (empty($key)) {
            return;
        }

        $keys = explode('.', $key);
        if (count($keys) === 1) {
            unset(self::$data[$key]);
            return;
        }

        $data = & self::$data;
        foreach ($keys as $key) {
            if (!isset($data[$key])) {
                return;
            }

            $data = & $data[$key];
        }

        unset($data);
    }

    /**
     * Check if key is accessing system configuration.
     * @param   string  $key    The key of the configuration.
     * @return  bool   `true` if the key is accessing system configuration, `false` otherwise.
     */
    final public static function isSystemConfig(string $key): bool
    {
        return strpos($key, 'system') === 0;
    }
}