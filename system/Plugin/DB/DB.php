<?php

namespace SkiddPH\Plugin\DB;

use Exception;
use PDO;
use PDOStatement;

class DB
{
    /**
     * Holds Multiple Database Connections
     * @var array
     */
    protected static $connections = [];
    /**
     * Connect to a database with a key and return an ORM instance.
     * @param   string  $key    The key of the database connection.
     * @return  PDO
     */
    final static function connect(string $key = null): PDO
    {
        $key = !empty($key) ? $key : pcfg('database.database', 'default');
        if (!isset(self::$connections[$key])) {
            $pdo_args = pcfg("database.databases.$key", null);
            if ($pdo_args === null) {
                throw new Exception("Database connection not found: $key");
            }
            self::$connections[$key] = new PDO(...$pdo_args);
        }

        return self::$connections[$key];
    }
    /**
     * Remove a database connection.
     * @param   string  $key    The key of the database connection.
     * @return  void
     */
    final static function disconnect(string $key): void
    {
        self::$connections[$key] = null;
        unset(self::$connections[$key]);
    }
    /**
     * Generate Phinx Environment Configs
     * @return array
     */
    final static function phinxConfig(): array
    {
        $databases = pcfg('database.databases', []);
        $phinx = [];
        foreach ($databases as $key => $value) {
            if (count($value) < 1) {
                throw new Exception('Invalid PDO Args');
            }

            $des = [];
            $parts = explode(';', $value[0]);
            $adapter = null;
            foreach ($parts as $part) {
                $part = explode('=', $part);
                $part_key = strtolower($part[0]);
                $part_val = $part[1];

                $key_parts = explode(':', $part_key);
                if (count($key_parts) > 1) {
                    $adapter = $key_parts[0];
                    $part_key = $key_parts[1];
                }

                $des[$part_key] = $part_val;
            }

            if ($adapter) {
                $des['adapter'] = $adapter;
            }

            if (isset($value[1])) {
                $des['user'] = $value[1];
            }

            if (isset($value[2])) {
                $des['pass'] = $value[2];
            }

            $convert = [
                "dbname" => "name",
            ];

            foreach ($convert as $ck => $cv) {
                if (isset($des[$ck])) {
                    $des[$cv] = $des[$ck];
                    $des[$ck] = null;
                    unset($des[$ck]);
                }
            }

            $phinx[$key] = [
                "adapter" => (string) @$des['adapter'],
                "host" => (string) @$des['host'],
                "name" => (string) @$des['name'],
                "user" => (string) @$des['user'],
                "pass" => (string) @$des['pass'],
                "port" => (string) @$des['port'] ?? '3306',
                "charset" => (string) @$des['charset'] ?? 'utf8',
            ];
        }

        $default_env = ["default", "development"];

        foreach ($default_env as $default) {
            if (isset($phinx[$default])) {
                $phinx['default_environment'] = $default;
                break;
            }
        }

        if (!isset($phinx['default_environment'])) {
            $keys = array_keys($phinx);
            $phinx['default_environment'] = $keys[0];
        }

        return $phinx;
    }
    /**
     * Generate a Raw SQL Query
     * @param   string  $sql    The SQL Query
     * @param   array   $params The Parameters
     * @return  Raw
     */
    final static function raw(string $sql, array $params = []): Raw
    {
        return new Raw($sql, $params);
    }
    /**
     * SQL Raw Query Executor
     * @param   string  $sql    The SQL Query
     * @param   array   $params The Parameters
     * @param   mixed   $pdo    The PDO Instance or the Key of the Database Connection. Can be a string for DB::connect, an SQL Arguments array, a PDO instance or null for default
     * @param   PDO|null   $ref    The Reference of the PDO Instance
     * @return  PDOStatement
     */
    final static function query($sql, $params, $pdo = null, &$ref = null)
    {
        if (empty($pdo) || !empty($pdo) && is_string($pdo)) {
            $pdo = static::connect(empty($pdo) ? null : $pdo);
        } else if (is_array($pdo)) {
            $pdo = new PDO(...$pdo);
        }

        if (!$pdo instanceof PDO) {
            throw new Exception("Invalid PDO Instance");
        }

        $ref = $pdo;
        $sql = new Raw($sql, $params);
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt;
    }
}