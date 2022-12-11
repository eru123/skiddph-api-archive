<?php

namespace Api\Core;

abstract class Plugin
{
    /**
     * Returns the plugin configuration
     * @return PluginConfig
     */
    abstract static function config(): PluginConfig;

    /**
     * Set and Get the plugin key
     * @param   string|null $key The plugin key
     * @return  string The plugin key
     */
    abstract static function key(string $key = null): string;
}
