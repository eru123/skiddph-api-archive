<?php

namespace Api\Database;

use PDO;
use PDOStatement;
use Exception;
use Api\Lib\Arr;

/**
 * Database Object-Relational Mapper
 */
class ORM extends Helper
{
    /**
     * Holds the query Array  
     * @var array
     */
    private $query = [];

    /**
     * Holds the last SQL query string
     * @var string
     */
    private $sql = '';

    /**
     * Holds the PDO instance
     * @var PDO
     */
    private $pdo;

    /**
     * Holds the PDOStatement instance
     * @var PDOStatement
     */
    private $stmt;

    /**
     * Holds the last query Array key
     * @var string
     */
    private $lastQueryKey = '';

    /**
     * Table info
     * @var array
     */
    private static $table;

    /**
     * Create a new instance of the ORM
     * @param PDO &$pdo
     */
    public function __construct(PDO &$pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get PDO instance
     */
    public function pdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Get PDOStatement instance
     */
    public function stmt(): PDOStatement
    {
        return $this->stmt;
    }

    /**
     * Use table
     * @param string $table
     * @param string $alias
     * @return self
     */
    public function table(string $table, string $alias = ''): self
    {
        $key = 'table';
        $this->query[$key] = $table . ($alias ? " AS $alias" : '');
        $this->lastQueryKey = $key;
        return $this;
    }

    /**
     * Select columns
     * @param string $column
     * @param string $alias
     * @return self
     */
    public function select(string $column, string $alias = ''): self
    {
        $key = 'select';
        if (!isset($this->query[$key])) {
            $this->query[$key] = [];
        }
        $this->query[$key][] = $column . ($alias ? ' AS ' . $alias : '');
        $this->lastQueryKey = $key;
        return $this;
    }

    /**
     * Select all columns
     */
    public function selectAll(): self
    {
        $key = 'select';
        $this->query[$key] = ['*'];
        $this->lastQueryKey = $key;
        return $this;
    }

    /**
     * Select distinct columns
     * @param string $column
     * @param string $alias
     * @return self
     */
    public function selectDistinct(string $column, string $alias = ''): self
    {
        $key = 'select';
        if (!isset($this->query[$key])) {
            $this->query[$key] = [];
        }
        $this->query[$key][] = 'DISTINCT ' . $column . ($alias ? ' AS ' . $alias : '');
        $this->lastQueryKey = $key;
        return $this;
    }

    /**
     * Select Function
     * @param string $function
     * @param string $column
     * @param string $alias
     * @return self
     */
    public function selectFunction(string $function, mixed $column, string $alias = ''): self
    {
        $key = 'select';
        if (!isset($this->query[$key])) {
            $this->query[$key] = [];
        }
        $this->query[$key][] = self::function($function, $column, $alias);
        $this->lastQueryKey = $key;
        return $this;
    }

    /**
     * Select count
     * @param string $column
     * @param string $alias
     * @return self
     */
    public function selectCount(string $column, string $alias = ''): self
    {
        return $this->selectFunction('COUNT', $column, $alias);
    }

    /**
     * Select max
     * @param string $column
     * @param string $alias
     * @return self
     */
    public function selectMax(string $column, string $alias = ''): self
    {
        return $this->selectFunction('MAX', $column, $alias);
    }

    /**
     * Select min
     * @param string $column
     * @param string $alias
     * @return self
     */
    public function selectMin(string $column, string $alias = ''): self
    {
        return $this->selectFunction('MIN', $column, $alias);
    }

    /**
     * Select sum
     * @param string $column
     * @param string $alias
     * @return self
     */
    public function selectSum(string $column, string $alias = ''): self
    {
        return $this->selectFunction('SUM', $column, $alias);
    }

    /**
     * Select avg
     * @param string $column
     * @param string $alias
     * @return self
     */
    public function selectAvg(string $column, string $alias = ''): self
    {
        return $this->selectFunction('AVG', $column, $alias);
    }

    /**
     * Select group by
     * @param string $column
     * @return self
     */
    public function group(string $column): self
    {
        $key = 'group';
        if (!isset($this->query[$key])) {
            $this->query[$key] = [];
        }
        $this->query[$key][] = $column;
        $this->lastQueryKey = $key;
        return $this;
    }

    /**
     * Select order by
     * @param string $column
     * @param string $order
     * @return self
     */
    public function order(string|array $column, string $order = 'ASC'): self
    {
        $key = 'order';
        if (!isset($this->query[$key])) {
            $this->query[$key] = [];
        }
        $this->query[$key] = array_merge($this->query[$key], is_array($column) ? $column : [$column . ' ' . $order]);
        $this->lastQueryKey = $key;
        return $this;
    }

    /**
     * Select limit
     * @param int $limit
     * @param int $offset
     * @return self
     */
    public function limit(int $limit, int $offset = -1): self
    {
        $key = 'limit';
        $this->query[$key] = $limit . ($offset > -1 ? " OFFSET $offset" : '');
        $this->lastQueryKey = $key;
        return $this;
    }

    /**
     * Select where
     * @param string|array $where
     * @return self
     */
    public function where(string|array $where): self
    {
        $key = 'where';
        if (!isset($this->query[$key])) {
            $this->query[$key] = [];
        }
        if (is_array($where)) {
            $this->query[$key] = array_merge($this->query[$key], $where);
        } else {
            $this->query[$key][] = $where;
        }
        $this->lastQueryKey = $key;
        return $this;
    }

    /**
     * OR
     * @return self
     */
    public function or(): self
    {
        $allowed = ['where', 'on', 'having'];
        if (in_array($this->lastQueryKey, $allowed)) {
            $key = $this->lastQueryKey;
            if (isset($this->query[$key]) && count($this->query[$key]) > 0) {
                $this->query[$key][] = 'OR';
            }
        }

        return $this;
    }

    /**
     * AND
     * @return self
     */
    public function and(): self
    {
        $allowed = ['where', 'on', 'having'];
        if (in_array($this->lastQueryKey, $allowed)) {
            $key = $this->lastQueryKey;
            if (isset($this->query[$key]) && count($this->query[$key]) > 0) {
                $this->query[$key][] = 'AND';
            }
        }
        return $this;
    }

    /**
     * Select having
     * @param string|array $having
     * @return self
     */
    public function having(string|array $having): self
    {
        $key = 'having';
        if (!isset($this->query[$key])) {
            $this->query[$key] = [];
        }
        if (is_array($having)) {
            $this->query[$key] = array_merge($this->query[$key], $having);
        } else {
            $this->query[$key][] = $having;
        }
        $this->lastQueryKey = $key;
        return $this;
    }

    /**
     * Select join
     * @param string $table
     * @param string $on
     * @param string $type
     * @return self
     */
    public function join(string $table, string $alias, string $type = 'INNER'): self
    {
        $key = 'join';
        if (!isset($this->query[$key])) {
            $this->query[$key] = [];
        }
        $this->query[$key][] = [
            'type' => $type,
            'table' => $table,
            'alias' => $alias,
            'on' => []
        ];
        $this->lastQueryKey = $key;
        return $this;
    }

    /**
     * Left join
     * @param string $table
     * @param string $alias
     * @return self
     */
    public function leftJoin(string $table, string $alias): self
    {
        return $this->join($table, $alias, 'LEFT');
    }

    /**
     * Right join
     * @param string $table
     * @param string $alias
     * @return self
     */
    public function rightJoin(string $table, string $alias): self
    {
        return $this->join($table, $alias, 'RIGHT');
    }

    /**
     * Full join
     * @param string $table
     * @param string $alias
     * @return self
     */
    public function fullJoin(string $table, string $alias): self
    {
        return $this->join($table, $alias, 'FULL');
    }

    /**
     * Select on
     * @param string|array $on
     * @return self
     */
    public function on(string|array $on): self
    {
        $key = 'on';
        $keys = ['join', 'on'];
        if (in_array($this->lastQueryKey, $keys)) {
            $key = $this->lastQueryKey;

            // get last join
            if (count($this->query['join']) > 0) {
                $lastJoin = array_pop($this->query['join']);
                if (is_array($on)) {
                    $lastJoin['on'] = array_merge($lastJoin['on'], $on);
                } else {
                    $lastJoin['on'][] = $on;
                }
                $this->query['join'][] = $lastJoin;
                $this->lastQueryKey = $key;
            }
        }
        return $this;
    }

    /**
     * Data
     * @param array $data
     * @return self
     */
    public function data(array $data): self
    {
        $key = 'data';
        $data = self::escape($this->pdo, $data);

        if (!isset($this->query[$key])) {
            $this->query[$key] = [];
        }

        if (self::isMultiArray($data)) {
            $this->query[$key] = array_merge($this->query[$key], $data);
        } else {
            $this->query[$key][] = $data;
        }
        $this->query[$key] = $data;
        $this->lastQueryKey = $key;
        return $this;
    }

    /**
     * Insert
     * @param array $data [optional] data to insert
     * @return self
     */
    public function insert($data = null): self
    {
        $this->filterInsertData();
        $this->query['action'] = 'insert';
        if (is_array($data)) {
            $this->data($data);
        }
        $this->sql = Parser::parse($this->query);
        $this->query = [];
        $this->lastQueryKey = '';
        $this->stmt = $this->pdo->prepare($this->sql);
        $this->stmt->execute();
        return $this;
    }

    /**
     * Update
     * @return PDOStatement
     */
    public function update(): PDOStatement
    {
        $this->filterInsertData();
        $this->query['action'] = 'update';
        $this->sql = Parser::parse($this->query);
        $this->query = [];
        $this->lastQueryKey = '';
        $this->stmt = $this->pdo->prepare($this->sql);
        $this->stmt->execute();
        return $this->stmt;
    }

    /**
     * Upsert MySQL
     * @return self
     */
    public function upsert(): self
    {
        $this->filterInsertData();
        $this->query['action'] = 'upsert';
        $this->sql = Parser::parse($this->query);
        $this->query = [];
        $this->lastQueryKey = '';
        $this->stmt = $this->pdo->prepare($this->sql);
        $this->stmt->execute();
        return $this;
    }

    /**
     * Quote and escape data
     * @param string $data
     * @return string
     */
    public function quote(string $data): string
    {
        return $this->pdo->quote($data);
    }

    /**
     * Delete
     * @return PDOStatement
     */
    public function delete(): PDOStatement
    {
        $this->query['action'] = 'delete';
        $this->sql = Parser::parse($this->query);
        $this->query = [];
        $this->lastQueryKey = '';
        $this->stmt = $this->pdo->prepare($this->sql);
        $this->stmt->execute();
        return $this->stmt;
    }

    /**
     * Get last insert id
     * @return int
     */
    public function lastInsertId(): int
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Get last query
     * @return string
     */
    public function getLastQuery(): string
    {
        return $this->sql;
    }

    /**
     * Get row count
     * @return int
     */
    public function rowCount(): int
    {
        return $this->stmt->rowCount();
    }

    /**
     * Read one
     * @return Arr
     */
    public function readOne(): Arr|null
    {
        $this->query['action'] = 'select';
        $this->sql = Parser::parse($this->query);
        $this->query = [];
        $this->lastQueryKey = '';
        $this->stmt = $this->pdo->prepare($this->sql);
        $this->stmt->execute();
        $res = $this->stmt->fetch(PDO::FETCH_ASSOC);
        return Arr::from(is_array($res) ? $res : []);
    }

    /**
     * Read many
     * @return Arr
     */
    public function readMany(): Arr
    {
        $this->query['action'] = 'select';
        $this->sql = Parser::parse($this->query);
        $this->query = [];
        $this->lastQueryKey = '';
        $this->stmt = $this->pdo->prepare($this->sql);
        $this->stmt->execute();
        $res = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
        return Arr::from(is_array($res) ? $res : []);
    }

    /**
     * Show tables of the current database
     * @return array
     */
    public function tables(): array
    {
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $this->sql = "SHOW TABLES";
        if ($driver == 'pgsql') {
            $this->sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'";
        }
        $this->stmt = $this->pdo->prepare($this->sql);
        $this->stmt->execute();
        return $this->stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Show columns
     * @return array
     */
    public function columns(): array
    {
        if (!isset($this->query['table'])) {
            throw new Exception('Table not set');
        }

        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $table = explode(' ', $this->query['table'])[0];
        if (strpos($table, '.') !== false) {
            $table = explode('.', $table)[1];
        }

        if (!empty(self::$table[$table]['columns'])) {
            return self::$table[$table]['columns'];
        }

        self::$table[$table]['columns'] = [];

        $this->sql = "SHOW COLUMNS FROM $table";
        if ($driver == 'pgsql') {
            $this->sql = "SELECT column_name FROM information_schema.columns WHERE table_name = '$table'";
        }
        $this->stmt = $this->pdo->prepare($this->sql);
        $this->stmt->execute();
        self::$table[$table]['columns'] = $this->stmt->fetchAll(PDO::FETCH_COLUMN);
        return self::$table[$table]['columns'];
    }

    /**
     * Show indexes
     * @return array
     */
    public function indexes(): array
    {
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $table = explode(' ', $this->query['table'])[0];
        $this->sql = "SHOW INDEXES FROM $table";
        if ($driver == 'pgsql') {
            $this->sql = "SELECT indexname FROM pg_indexes WHERE tablename = '$table'";
        }
        $this->stmt = $this->pdo->prepare($this->sql);
        $this->stmt->execute();
        return $this->stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Show foreign keys
     * @return array
     */
    public function foreignKeys(): array
    {
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $table = explode(' ', $this->query['table'])[0];
        $this->sql = "SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = '$table'";
        if ($driver == 'pgsql') {
            $this->sql = "SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = '$table'";
        }
        $this->stmt = $this->pdo->prepare($this->sql);
        $this->stmt->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Show create table
     * @return string
     */
    public function createTableQuery(): string
    {
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $table = explode(' ', $this->query['table'])[0];
        $this->sql = "SHOW CREATE TABLE $table";
        if ($driver == 'pgsql') {
            $this->sql = "SELECT table_name, column_name, data_type, character_maximum_length, is_nullable, column_default, ordinal_position FROM information_schema.columns WHERE table_name = '$table' ORDER BY ordinal_position";
        }
        $this->stmt = $this->pdo->prepare($this->sql);
        $this->stmt->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC)['Create Table'];
    }

    /**
     * Show create index
     * @return string
     */
    public function createIndexQuery(): string
    {
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $table = explode(' ', $this->query['table'])[0];
        $index = $this->query['index'];
        $this->sql = "SHOW CREATE INDEX $index ON $table";
        if ($driver == 'pgsql') {
            $this->sql = "SELECT indexdef FROM pg_indexes WHERE indexname = '$index'";
        }
        $this->stmt = $this->pdo->prepare($this->sql);
        $this->stmt->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC)['Create Index'];
    }

    /**
     * Begin transaction
     * @return self
     */
    public function begin(): self
    {
        $this->pdo->beginTransaction();
        return $this;
    }

    /**
     * Commit transaction
     * @return self
     */
    public function commit(): self
    {
        $this->pdo->commit();
        return $this;
    }

    /**
     * Rollback transaction
     * @return self
     */
    public function rollback(): self
    {
        $this->pdo->rollBack();
        return $this;
    }

    public function filterInsertData(): self
    {
        $columns = $this->columns();
        $data = [];
        foreach ($this->query['data'] as $d) {
            $data[] = array_intersect_key($d, array_flip($columns));
        }
        $this->query['data'] = $data;
        return $this;
    }

    /**
     * Inject Class
     * @param string $query
     * @param mixed ...$params
     * @return Inject
     */
    public function f(string $query, ...$params): Inject
    {
        return new Inject($this->pdo, $query, ...$params);
    }

    /**
     * Clear query
     * @return self
     */
    public function clear(): self
    {
        $this->query = [];
        return $this;
    }
}
