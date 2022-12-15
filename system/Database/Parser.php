<?php

namespace Api\Database;

use Exception;

class Parser
{
    final public static function parse(array $query): string
    {
        echo "QUERY: ", print_r($query, true), PHP_EOL;
        $action = @$query['action'];
        $action = "action_" . strtolower($action);
        try {
            $sql = @self::{$action}($query);
        } catch (Exception $e) {
            throw new Exception("Invalid query action: $action");
        }
        echo "SQL: ", $sql, PHP_EOL;
        return $sql;
    }

    final public static function action_insert(array $query): string
    {
        $table = @$query['table'];
        $data = @$query['data'];
        $columns = array_keys($data[0]);
        $values = array_map(function ($row) {
            return array_values($row);
        }, $data);
        $columns = implode(', ', $columns);
        $values = implode(', ', array_map(function ($row) {
            return '(' . implode(', ', $row) . ')';
        }, $values));
        return "INSERT INTO $table ($columns) VALUES $values";
    }

    final public static function action_update(array $query): string
    {
        $table = @$query['table'];
        $data = @$query['data'];
        $where = @$query['where'];
        $columns = array_keys($data[0]);
        $values = array_map(function ($row) {
            return array_values($row);
        }, $data);
        $columns = implode(', ', $columns);
        $values = implode(', ', array_map(function ($row) {
            return '(' . implode(', ', $row) . ')';
        }, $values));

        if (is_array($where)) {
            $where = self::parse_where($where);
        }

        return "UPDATE $table SET ($columns) = $values WHERE $where";
    }

    final public static function parse_where(array $where): string
    {
        $sql = [];
        foreach ($where as $key => $value) {
            // if is numeric, then is a key
            if (is_numeric($key)) {
                if (is_array($value)) {
                    $sql[] = "(" . self::parse_where($value) . ")";
                } else {
                    $sql[] = $value;
                }
            } else if (is_string($key)) {
                if (is_array($value)) {
                    $sql[] = Helper::isMultiArray($value) ? "(" . Helper::condition($key, $value) . ")" : Helper::condition($key, $value);
                } else {
                    $sql[] = "$key = $value";
                }
            }
        }



        return implode(' ', $sql);
    }
}
