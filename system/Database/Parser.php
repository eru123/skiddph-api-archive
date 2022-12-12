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
}
