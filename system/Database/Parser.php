<?php

namespace Api\Database;

use Exception;

class Parser
{
    final public static function parse(array $query): string
    {
        // echo "QUERY: ", print_r($query, true), PHP_EOL;
        $query = self::filterData($query);
        $action = @$query['action'];
        $action = "action_" . strtolower($action);
        try {
            $sql = @self::{$action}($query);
        } catch (Exception $e) {
            throw new Exception("Invalid query action: $action");
        }
        // echo "SQL: ", $sql, PHP_EOL;
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

        $set = [];
        foreach ($data as $value) {
            foreach ($value as $key => $val) {
                $set[] = "$key = $val";
            }
        }

        $set = implode(', ', $set);
        $where = is_array($where) ? self::parse_where($where) : $where;
        return "UPDATE $table SET $set WHERE $where";
    }

    final public static function parse_where(array $where): string
    {
        $sql = [];
        foreach ($where as $key => $value) {
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

    final public static function action_delete(array $query): string
    {
        $table = @$query['table'];
        $where = @$query['where'];
        $where = is_array($where) ? self::parse_where($where) : $where;
        return "DELETE FROM $table WHERE $where";
    }

    final public static function action_select(array $query): string
    {
        $table = @$query['table'];
        $columns = @$query['select'];
        $where = @$query['where'];
        $where = is_array($where) ? self::parse_where($where) : $where;
        $columns = is_array($columns) ? implode(', ', $columns) : $columns;
        return "SELECT $columns FROM $table WHERE $where";
    }

    final public static function filterData(array $query): array
    {
        if (isset($query['data'])) {
            $data = $query['data'];
            $query['data'] = Helper::isMultiArray($data) ? $data : [$data];
        }

        return $query;
    }
}
