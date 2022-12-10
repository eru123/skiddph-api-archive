<?php

namespace Api\Core;

abstract class Plugin
{
    /**
     * Returns the plugin configuration
     * @return PluginConfig
     */
    abstract static function config(): PluginConfig;
}
