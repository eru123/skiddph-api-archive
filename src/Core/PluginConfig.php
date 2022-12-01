<?php

namespace Core;

/**
 * 
 */
class PluginConfig
{
    /**
     * Holds the static key-value pairs of the configuration.
     * @var     array
     */
    private static $data;

    /**
     * Holds the current plugin key.
     * @var     string
     */
    private $plugin;

    /**
     * create a new instance of the plugin config
     * @param string $pluginName
     * @return self
     */
    public function __construct(string $pluginName)
    {
        self::$plugin = $pluginName;
        if (!isset(self::$data[$pluginName])) {
            self::$data[$pluginName] = [];
        }
    }

    /**
     * Set a configuration value.
     * @param   string  $key    The key of the configuration.
     * @param   mixed   $value  The value of the configuration.
     * @return  self
     */
    final public function set(string $key, mixed $value): self
    {   
        if(!isset(self::$data[$this->plugin])){
            self::$data[$this->plugin] = [];
        }
        self::$data[self::$plugin][$key] = $value;
        return $this;
    }

    /**
     * Get a configuration value.
     * @param   string  $key    The key of the configuration.
     * @param   mixed   $default    The default value to return if the key is not found.
     * @return  mixed  The value of the configuration.
     */
    final public function get(string $key, mixed $default = null): mixed
    {
        return self::$data[self::$plugin][$key] ?? $default;
    }

    /**
     * Check if a configuration key exists.
     * @param   string  $key    The key of the configuration.
     * @return  bool   `true` if the key exists, `false` otherwise.
     */
    final public function has(string $key): bool
    {
        return isset(self::$data[self::$plugin][$key]);
    }

    /**
     * Returns the entire configuration array.
     * @return array   The configuration array.
     */
    final public function all(): array
    {
        return self::$data[self::$plugin];
    }

    /**
     * Remove a configuration key.
     * @param   string  $key    The key of the configuration.
     * @return  self
     */
    final public function delete(string $key): self
    {
        unset(self::$data[self::$plugin][$key]);
        return $this;
    }

    /**
     * Clear the entire configuration array.
     * @return  self
     */
    final public function clear(): self
    {
        self::$data[self::$plugin] = [];
        return $this;
    }

    /**
     * Destroy the entire configuration array.
     * @return  void
     */
    final public function destroy(): void
    {
        unset(self::$data[self::$plugin]);
    }

    /**
     * Check if a configuration key exists.
     * @param   string  $key    The key of the configuration.
     * @return  bool   `true` if the key exists, `false` otherwise.
     */
    final public static function hasPlugin(string $pluginName): bool
    {
        return isset(self::$data[$pluginName]);
    }
}
