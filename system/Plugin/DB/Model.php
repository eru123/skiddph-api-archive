<?php

namespace SkiddPH\Plugin\DB;

abstract class Model
{
    protected $table = null;
    protected $primary_key = 'id';
    protected $query = [];

    final static function __callStatic($name, $arguments)
    {
        $fun = "f__.$name";
        $obj = new static();
        if (method_exists($obj, $fun)) {
            return call_user_func_array([$obj, $fun], $arguments);
        }

        throw new \Exception("Method not found: $name");
    }

    final function __call($name, $arguments)
    {
        $funcname = "f__.$name";
        if (method_exists($this, $funcname)) {
            return call_user_func_array([$this, $funcname], $arguments);
        }

        throw new \Exception("Method not found: $name");
    }
}
