<?php

namespace SkiddPH\Plugin\Database;

abstract class Model
{
    private $orm;
    private $tb;

    public function __construct(ORM $orm, string $tb)
    {
        $this->orm = $orm;
        $this->tb = $tb;
    }

    function create(array $data)
    {
        return $this->orm
            ->table($this->tb)
            ->data($data)
            ->insert();
    }

    function update(array $where, array $data)
    {
        return $this->orm
            ->table($this->tb)
            ->where($where)
            ->data($data)
            ->update();
    }

    function delete(array $where)
    {
        return $this->orm
            ->table($this->tb)
            ->where($where)
            ->delete();
    }

    function get(array $where)
    {
        return $this->orm
            ->table($this->tb)
            ->where($where)
            ->readOne();
    }

    function all(array $where)
    {
        return $this->orm
            ->table($this->tb)
            ->where($where)
            ->readMany();
    }
}
