<?php

namespace App;

class Config
{
    /**
     * Holds the static key-value pairs of the configuration.
     * @var     array
     */
    private static $data;

    /**
     * Set a configuration value.
     * @param   string  $key    The key of the configuration.
     * @param   mixed   $value  The value of the configuration.
     * @return  void
     */
    final public static function set(string $key, mixed $value): void
    {
        self::$data[$key] = $value;
    }

    /**
     * Get a configuration value.
     * @param   string  $key    The key of the configuration.
     * @param   mixed   $default    The default value to return if the key is not found.
     * @return  mixed
     */
    final public static function get(string $key, mixed $default = null): mixed
    {
        return self::$data[$key] || $default;
    }

    /**
     * Check if a configuration key exists.
     * @param   string  $key    The key of the configuration.
     * @return  bool   `true` if the key exists, `false` otherwise.
     */
    final public static function has(string $key): bool
    {
        return isset(self::$data[$key]);
    }

    /**
     * Returns the entire configuration array.
     * @return array   The configuration array.
     */
    final public static function all(): array
    {
        return self::$data;
    }

    /**
     * Remove a configuration key.
     * @param   string  $key    The key of the configuration.
     * @return  void
     */
    final public static function delete(string $key): void
    {
        unset(self::$data[$key]);
    }

    /**
     * Clear the entire configuration array.
     * @return  void
     */
    final public static function clear(): void
    {
        self::$data = [];
    }
}
