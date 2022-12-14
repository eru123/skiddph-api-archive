<?php

use Exception;
use PDO;
use PluginConfig;

use Api\Database\{
    ORM,
    Helper
};

class Database implements PluginKey
{
    private static $key = "DATABASES";

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
     * Holds Multiple Database Connections
     * @var array
     */
    private static $connections = [];

    /**
     * Connect to a database with a key and return an ORM instance.
     * @param   string  $key    The key of the database connection.
     * @return  ORM
     */
    final static function connect(string $key): ORM
    {
        if (!isset(self::$connections[$key])) {
            $cfg = self::config();
            $pdo_args = $cfg->get($key);
            if ($pdo_args === null) {
                throw new Exception("Database connection not found: $key");
            }
            self::$connections[$key] = new PDO(...$pdo_args);
        }

        return new ORM(self::$connections[$key]);
    }

    /**
     * Remove a database connection.
     * @param   string  $key    The key of the database connection.
     * @return  void
     */
    final static function disconnect(string $key): void
    {
        if (isset(self::$connections[$key])) {
            unset(self::$connections[$key]);
        }
    }

    /**
     * Generate Phinx Environment Configs
     * @return array
     */
    final static function phinxConfig(): array
    {
        $cfg = self::config()->all();
        return Helper::toPhinxConfig($cfg);
    }
}
