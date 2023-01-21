<?php

namespace SkiddPH\Plugin\DB;

use Exception;
use PDO;

abstract class Model
{
    protected $table = null;
    protected $primary_key = null;
    protected $fillable = [];
    protected $query = [
        'select' => null,
        'where' => null,
        'order' => null,
        'limit' => null,
        'offset' => null,
        'group' => null,
        'having' => null,
        'joins' => null,
        'data' => null,
    ];

    protected $last_query = null;

    protected $last_call = null;

    /**
     * PDO variable can be a string for DB::connect(), a PDO Argument array, or a PDO instance.
     * @var string|array|PDO
     */
    protected $pdo = 'default';

    final public static function __callStatic($name, $arguments)
    {
        $fun = "f__$name";
        $obj = new static();
        if (method_exists($obj, $fun)) {
            return call_user_func_array([$obj, $fun], $arguments);
        }

        throw new Exception("Method not found: $name");
    }

    final public function __call($name, $arguments)
    {
        $fun = "f__$name";
        if (method_exists($this, $fun)) {
            return call_user_func_array([$this, $fun], $arguments);
        }

        throw new Exception("Method not found: $name");
    }

    /**
     * Get PDO instance
     * @throws Exception
     * @return PDO
     */
    final protected function f__pdo()
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        if (is_string($this->pdo)) {
            $this->pdo = DB::connect($this->pdo);
            return $this->pdo;
        }

        if (is_array($this->pdo)) {
            $this->pdo = new PDO(...$this
                ->pdo);
            return $this->pdo;
        }

        throw new Exception('Invalid PDO value for Model');
    }

    /**
     * Summary of Wwhere
     * @param Raw|string        $key
     * @param string|null       $operator
     * @param array|string|null $value
     * @param string            $conjuction
     * @return static
     */
    final protected function f__where($key, $operator = null, $value = null, $conjuction = 'AND')
    {
        if ($this->last_call !== 'where') {
            $this->query['where'] = [];
        } else {
            $this->query['where'][] = $conjuction;
        }

        $this->last_call = 'where';

        if (!$key instanceof Raw) {
            $key = "`$key`";
        }

        if (!is_null($operator) and is_null($value)) {
            $value = $operator;
            $operator = $value instanceof Raw ? '' : '=';
        }

        $operator = strtoupper($operator);

        if (in_array($operator, ['BETWEEN']) && is_array($value)) {
            $operator = 'BETWEEN';
            $value = new Raw(str_repeat('? AND ', count($value) - 1) . '?', $value);
        } else if (in_array($operator, ['NOT BETWEEN', '!BETWEEN', 'NOT_BETWEEN']) && is_array($value)) {
            $operator = 'NOT BETWEEN';
            $value = new Raw(str_repeat('? AND ', count($value) - 1) . '?', $value);
        } else if (in_array($operator, ['IN']) && is_array($value)) {
            $operator = 'IN';
            $value = new Raw('(' . str_repeat('?,', count($value) - 1) . '?)', $value);
        } else if (in_array($operator, ['NOT IN', '!IN', 'NOT_IN']) && is_array($value)) {
            $operator = 'NOT IN';
            $value = new Raw('(' . str_repeat('?,', count($value) - 1) . '?)', $value);
        } else if (in_array($operator, ['IS NULL', 'IS_NULL', 'ISNULL', 'NULL'])) {
            $operator = 'IS NULL';
            $value = null;
        } else if (in_array($operator, ['IS NOT NULL', 'IS_NOT_NULL', 'ISNOTNULL', '!NULL'])) {
            $operator = 'IS NOT NULL';
            $value = null;
        } else if (in_array($operator, ['>=', 'GTE'])) {
            $operator = '>=';
        } else if (in_array($operator, ['>', 'GT', 'G'])) {
            $operator = '>';
        } else if (in_array($operator, ['<=', 'LTE'])) {
            $operator = '<=';
        } else if (in_array($operator, ['<', 'LT', 'L'])) {
            $operator = '<';
        }

        if (is_string($value) && !$value instanceof Raw) {
            $value = new Raw('?', [$value]);
        } else if (is_array($value) && !$value instanceof Raw) {
            $value = new Raw('(' . str_repeat('?,', count($value) - 1) . '?)', $value);
        } else if (!is_null($value) && !$value instanceof Raw) {
            $value = new Raw('?', [$value]);
        }

        $this->query['where'][] = new Raw("$key $operator $value");
        return $this;
    }

    /**
     * Limit the number of results returned.
     * @param int $limit
     * @param int $offset
     * @return static
     */
    final protected function f__limit($limit, $offset = -1)
    {
        $this->query['limit'] = $limit;
        if ($offset > -1) {
            $this->query['offset'] = $offset;
        }
        return $this;
    }

    /**
     * Offset the number of results returned.
     * @param int $offset
     * @return static
     */
    final protected function f__offset($offset)
    {
        $this->query['offset'] = $offset;
        return $this;
    }

    /**
     * Select the columns to return.
     * @param array<array|string|Raw> $columns
     * @return static
     */
    final protected function f__select(...$columns)
    {
        if (count($columns) === 1) {
            $columns = $columns[0];
        }

        if ($columns instanceof Raw) {
            $this->query['select'] = $columns;
            return $this;
        }

        if (is_array($columns)) {
            $cols = array_map(function ($column) {
                if ($column instanceof Raw) {
                    return $column;
                }
                return "`$column`";
            }, $columns);
            $this->query['select'] = DB::raw(implode(', ', $cols));
            return $this;
        }

        if (is_string($columns)) {
            $this->query['select'] = DB::raw($columns);
            return $this;
        }

        throw new Exception('Invalid select columns');
    }

    /**
     * Order the results by a column.
     * @param string|Raw $column
     * @param string|null $direction
     * @return static
     */
    final protected function f__order($column, $direction = null)
    {
        if (empty($this->query['order'])) {
            $this->query['order'] = [];
        }

        if ($column instanceof Raw) {
            $this->query['order'][] = $column;
            return $this;
        }

        if (is_null($direction)) {
            $direction = 'ASC';
        }

        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'])) {
            throw new Exception('Invalid order direction');
        }

        $this->query['order'][] = new Raw("`$column` $direction");
        return $this;
    }

    final protected function f__get_select()
    {
        if (empty($this->query['select'])) {
            return 'SELECT *';
        }

        if (is_array($this->query['select']) || is_string($this->query['select'])) {
            $this->f__select($this->query['select']);
        }

        if ($this->query['select'] instanceof Raw) {
            return 'SELECT ' . $this->query['select'];
        }

        throw new Exception('Invalid select columns');
    }

    final protected function f__get_where()
    {
        if (empty($this->query['where'])) {
            return 'WHERE 1';
        }

        if (is_array($this->query['where'])) {
            return 'WHERE ' . (!empty($this->query['where']) ? implode(' ', $this->query['where']) : '1');
        }

        return 'WHERE ' . $this->query['where'];
    }

    final protected function f__get_from()
    {
        if (empty($this->table)) {
            throw new Exception('Table not set');
        }

        return "FROM $this->table";
    }

    final protected function f__get_limit()
    {
        if (empty($this->query['limit'])) {
            return '';
        }

        $query = "LIMIT " . $this->query['limit'];
        if (!empty($this->query['offset'])) {
            $query .= " OFFSET " . $this->query['offset'];
        }

        return $query;
    }

    final protected function f__get_order()
    {
        if (empty($this->query['order'])) {
            return '';
        }

        return 'ORDER BY ' . implode(', ', $this->query['order']);
    }

    final protected function f__lastQuery()
    {
        return $this->last_query;
    }

    final protected function f__getSql()
    {
        $select = $this->f__get_select();
        $from = $this->f__get_from();
        $where = $this->f__get_where();
        $limit = $this->f__get_limit();
        $order = $this->f__get_order();
        return "$select $from $where $order $limit";
    }

    final protected function f__get(...$where)
    {   
        if (!empty($where)) {
            $this->f__where(...$where);
        }

        $this->last_call = 'get';
        $query = $this->f__getSql();
        $this->last_query = $query;
        $pdo = $this->f__pdo();
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    final protected function f__first(...$where)
    {
        if (!empty($where)) {
            $this->f__where(...$where);
        }

        $this->last_call = 'first';
        $query = $this->f__getSql();
        $this->last_query = $query;
        $pdo = $this->f__pdo();
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    final protected function f__find(...$where)
    {       
        if (empty($where)) {
            throw new Exception('Please provide id to find or a where arguments');
        }

        if (empty($this->primary_key)) {
            throw new Exception('Primary key not set');
        }

        if (empty($this->table)) {
            throw new Exception('Table not set');
        }

        if (count($where) === 1 && is_numeric($where[0])) {
            $where = [$this->primary_key, $where[0]];
        }

        $this->f__where(...$where);
        $this->last_call = 'find';
        $query = $this->f__getSql();
        $this->last_query = $query;
        $pdo = $this->f__pdo();
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
