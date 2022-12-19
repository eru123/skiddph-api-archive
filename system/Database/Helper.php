<?php

namespace Api\Database;

use Exception;
use PDO;

/**
 * ORM Helper Functions
 */
class Helper
{
    /**
     * Returns an SQL string from an array
     * @param string $function
     * @param string|array $columns
     * @param string $alias
     * @return string
     */
    public static function function(string $function, string|array $columns, string $alias = ''): string
    {
        $columns = is_array($columns) ? implode(', ', $columns) : $columns;
        return "$function($columns)" . ($alias ? " AS $alias" : '');
    }

    /**
     * Returns an SQL String of a group of CONDITION encapsulated in parenthesis and separated by an OPERATOR
     * @param array $conditions
     * @param string $operator
     * @return string
     */
    public static function conditions(array $conditions, string $operator = 'AND'): string
    {
        $conditions = array_map(function ($condition) {
            return "($condition)";
        }, $conditions);
        return implode(" $operator ", $conditions);
    }

    /**
     * Returns an SQL String of a CONDITION
     * @param string $column
     * @param array $condition
     * @return string
     */
    public static function condition(string $column, mixed $condition, string $operator = 'AND'): string
    {
        if (is_null($condition) || is_numeric($condition) || is_string($condition)) {
            return "$column = $condition";
        } else if (is_bool($condition)) {
            return "$column = " . ($condition ? 1 : 0);
        }

        if (!is_array($condition)) {
            throw new Exception('Invalid CONDITION');
        }

        $result = [];
        $conds = [
            'eq' => '=',
            '=' => '=',
            'is' => '=',
            'neq' => '!=',
            '!=' => '!=',
            'gt' => '>',
            '>' => '>',
            'gte' => '>=',
            '>=' => '>=',
            'lt' => '<',
            '<' => '<',
            'lte' => '<=',
            '<=' => '<=',
            'like' => 'LIKE',
            'notlike' => 'NOT LIKE',
            'not_like' => 'NOT LIKE',
            'in' => 'IN',
            'notin' => 'NOT IN',
            'not_in' => 'NOT IN',
            'between' => 'BETWEEN',
            'notbetween' => 'NOT BETWEEN',
            'not_between' => 'NOT BETWEEN',
            'isnull' => 'IS NULL',
            'is_null' => 'IS NULL',
            'isnotnull' => 'IS NOT NULL',
            'is_not_null' => 'IS NOT NULL',
        ];

        foreach ($condition as $key => $value) {
            $value = is_array($value) ? "(" . implode(', ', $value) . ")" : $value;
            if (is_numeric($key)) {
                $result[] = self::condition($column, $value);
            } else if (isset($conds[$key])) {
                $result[] = "$column {$conds[$key]} $value";
            } else if (is_string($key)) {
                $result[] = "$column $key $value";
            } else {
                throw new Exception('Invalid CONDITION');
            }
        }

        return "(" . implode(" $operator ", $result) . ")";
    }

    /**
     * Convert value to SQL string
     * @param mixed $value
     * @return mixed
     */
    public static function escape(PDO $pdo, mixed $value): mixed
    {
        if (is_null($value)) {
            return 'NULL';
        } else if (is_bool($value)) {
            return $value ? 1 : 0;
        } else if (is_numeric($value)) {
            return $value;
        } else if (is_string($value)) {
            return $pdo->quote($value);
        } else if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = self::escape($pdo, $val);
            }
        } else if (is_object($value) && method_exists($value, 'query')) {
            return $value->query();
        }

        return $value;
    }

    /**
     * Checks if data is a multi-dimensional array
     * @param mixed $data
     * @return bool
     */
    public static function isMultiArray(mixed $data): bool
    {
        if (is_array($data)) {
            foreach ($data as $value) {
                if (is_array($value)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Deserialize PDO DSN string into an array
     * @param string $dsn PDO DSN
     * @return array
     */
    public static function dsnDeserialize(string $dsn): array
    {
        $result = [];
        $parts = explode(';', $dsn);
        $adapter = null;
        foreach ($parts as $part) {
            $part = explode('=', $part);
            $key = strtolower($part[0]);
            $value = $part[1];

            // check if has key has adapter 
            $key_parts = explode(':', $key);
            if (count($key_parts) > 1) {
                $adapter = $key_parts[0];
                $key = $key_parts[1];
            }

            $result[$key] = $value;
        }

        if ($adapter) {
            $result['adapter'] = $adapter;
        }

        return $result;
    }

    /**
     * Deserialize PDO Args into array
     * @param array $args PDO Args
     * @return array
     */
    public static function pdoArgsDeserialize(array $args): array
    {
        if (count($args) < 1) {
            throw new Exception('Invalid PDO Args');
        }

        $result = self::dsnDeserialize($args[0]);

        if (isset($args[1])) {
            $result['user'] = $args[1];
        }

        if (isset($args[2])) {
            $result['pass'] = $args[2];
        }

        $convert = [
            "dbname" => "name",
        ];

        foreach ($convert as $key => $value) {
            if (isset($result[$key])) {
                $result[$value] = $result[$key];
                unset($result[$key]);
            }
        }

        return $result;
    }

    /**
     * Databases Config to Phinx Config
     * @param array $config Databases Config
     * 
     */
    public static function toPhinxConfig(array $config): array
    {
        $phinx = [];
        foreach ($config as $key => $value) {
            $des = self::pdoArgsDeserialize($value);
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
}
