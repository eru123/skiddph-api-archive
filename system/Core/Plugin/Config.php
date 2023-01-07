<?php

namespace SkiddPH\Core\Plugin;

use SkiddPH\Core\Config as CoreConfig;

class Config
{
    /**
     * Holds the config prefix
     * @var     string
     */
    private $cfgPrefix;

    /**
     * create a new instance of the plugin config
     * @param string $pluginName
     * @return self
     */
    public function __construct(string $pluginName)
    {
        $this->cfgPrefix = "system.plugins.$pluginName";
        $data = CoreConfig::get($this->cfgPrefix);
        if (empty($data) && !is_array($data)) {
            CoreConfig::set($this->cfgPrefix, []);
        }
    }

    /**
     * Set a configuration value.
     * @param   string  $key    The key of the configuration.
     * @param   mixed $value  The value of the configuration.
     * @return  self
     */
    final public function set(string $key, $value): self
    {
        CoreConfig::set("$this->cfgPrefix.$key", $value);
        return $this;
    }

    /**
     * Get a configuration value.
     * @param   string  $key    The key of the configuration.
     * @param   mixed   $default    The default value to return if the key is not found.
     * @return  mixed  The value of the configuration.
     */
    final public function get(string $key, mixed $default = null)
    {
        return CoreConfig::get("$this->cfgPrefix.$key", $default);
    }

    /**
     * Check if a configuration key exists.
     * @param   string  $key    The key of the configuration.
     * @return  bool   `true` if the key exists, `false` otherwise.
     */
    final public function has(string $key): bool
    {
        return CoreConfig::has("$this->cfgPrefix.$key");
    }

    /**
     * Returns the entire configuration array.
     * @return array   The configuration array.
     */
    final public function all(): array
    {
        return CoreConfig::get($this->cfgPrefix);
    }

    /**
     * Remove a configuration key.
     * @param   string  $key    The key of the configuration.
     * @return  self
     */
    final public function delete(string $key): self
    {
        CoreConfig::delete("$this->cfgPrefix.$key");
        return $this;
    }

    /**
     * Clear the entire configuration array.
     * @return  self
     */
    final public function clear(): self
    {
        CoreConfig::set($this->cfgPrefix, []);
        return $this;
    }

    /**
     * Destroy the entire configuration array.
     * @return  void
     */
    final public function destroy(): void
    {
        CoreConfig::delete($this->cfgPrefix);
    }

    /**
     * Check if a configuration key exists.
     * @param   string  $key    The key of the configuration.
     * @return  bool   `true` if the key exists, `false` otherwise.
     */
    final public static function hasPlugin(string $pluginName): bool
    {
        return CoreConfig::has("system.plugins.$pluginName");
    }
}