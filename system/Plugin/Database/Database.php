<?php

namespace SkiddPH\Plugin\Database;

use Exception;
use PDO;

class Database
{
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
    final static function connect(string $key = null): ORM
    {
        $key = $key ?? pcfg('database.database', 'default');
        if (!isset(self::$connections[$key])) {
            $pdo_args = pcfg("database.databases.$key", null);
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
        $cfg = pcfg('database.databases', []);
        return Helper::toPhinxConfig($cfg);
    }
}