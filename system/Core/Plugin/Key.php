<?php

namespace SkiddPH\Core\Plugin;

interface Key
{
    /**
     * Returns the plugin configuration
     * @return Config
     */
    static function config(): Config;

    /**
     * Set and Get the plugin key
     * @param string|null $key If set, the plugin key will be set to this value, else the current plugin key will be returned.
     * @return string The plugin key
     */
    static function key(string $key = null): string;
}
