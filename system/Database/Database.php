<?php

namespace Api\Database;

use Api\Core\Plugin;
use Api\Core\PluginConfig;
use Exception;
use PDO;

class Database extends Plugin
{
    static function config(): PluginConfig
    {
        return new PluginConfig('DATABASES');
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
