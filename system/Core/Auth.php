<?php

use Plugin;
use PluginConfig;
use Database;

use Api\Database\{
    ORM
};

class Auth extends Plugin
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

    /**
     * Returns the database ORM instance
     * @return ORM
     */
    final static function db(): ORM
    {
        $cfg = self::config();
        return Database::connect($cfg->get('DB_ENV'));
    }
}
