<?php

abstract class Plugin
{
    /**
     * Returns the plugin configuration
     * @return PluginConfig
     */
    abstract static function config(): PluginConfig;

    /**
     * Set and Get the plugin key
     * @param string|null $key If set, the plugin key will be set to this value, else the current plugin key will be returned.
     * @return string The plugin key
     */
    abstract static function key(string $key = null): string;
}
