<?php

namespace Api\Core;

use Api\Database\ORM;
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
    final static function connect(string $key): ORM
    {
        if (!isset(self::$connections[$key])) {
            $cfg = new PluginConfig('DATABASES');
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
}
