<?php

use Plugin;
use PluginConfig;
use Database;

use Api\Database\{
    ORM
};

use Api\Auth\{
    JWT,
    Password
};

class Auth implements PluginDB, PluginKey
{
    private static $key = "AUTHENTICATION";

    static function key(string $key = null): string
    {
        if (is_string($key) && !empty($key)) {
            self::$key = $key;
        }

        return self::$key;
    }

    static function config(): PluginConfig
    {
        return new PluginConfig(self::$key);
    }

    final static function db(): ORM
    {
        $cfg = self::config();
        return Database::connect($cfg->get('DB_ENV'));
    }
}
