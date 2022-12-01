<?php

namespace Database;

use Exception;

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

        return implode(" $operator ", $result);
    }
}
