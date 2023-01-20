<?php

namespace SkiddPH\Plugin\DB;

use Exception;
use PDO;

abstract class Model
{
    protected $table = null;
    protected $primary_key = null;
    protected $query_default = [
        'select' => '*',
        'where' => null,
        'order' => null,
        'limit' => null,
        'offset' => null,
    ];
    protected $query = [];

    /**
     * PDO variable can be a string for DB::connect(), a PDO Argument array, or a PDO instance.
     * @var string|array|PDO
     */
    protected $pdo = 'default';

    final static function __callStatic($name, $arguments)
    {
        $fun = "f__.$name";
        $obj = new static();
        if (method_exists($obj, $fun)) {
            return call_user_func_array([$obj, $fun], $arguments);
        }

        throw new Exception("Method not found: $name");
    }

    final function __call($name, $arguments)
    {
        $funcname = "f__.$name";
        if (method_exists($this, $funcname)) {
            return call_user_func_array([$this, $funcname], $arguments);
        }

        throw new Exception("Method not found: $name");
    }

    final function f__pdo(){
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        if (is_string($this->pdo)) {
            $this->pdo = DB::connect($this->pdo);
            return $this->pdo;
        }

        if (is_array($this->pdo)) {
            $this->pdo = new PDO(...$this->pdo);
            return $this->pdo;
        }

        throw new Exception('Invalid PDO value for Model');
    }

    final function f__where($key, $value)
    {
        $this->query['where'] = [$key, $value];
        return $this;
    }

    final function f__get()
    {
        return $this->query;
    }
}
