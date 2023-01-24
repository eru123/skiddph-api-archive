<?php

namespace SkiddPH\Plugin\DB;

use Exception;
use ArrayIterator;

class Rows extends ArrayIterator
{
    private $rows = array();
    private $position = 0;
    protected $model;

    /**
     * @param Model $model
     * @param array<array|Row> $rows
     */
    public function __construct($model, $rows)
    {
        $this->model = $model;
        $tmp = array();
        foreach ($rows as $row) {
            if ($row instanceof Row) {
                $tmp[] = $row;
            } else {
                $tmp[] = new Row($model, $row);
            }
        }
        $this->rows = $tmp;
    }

    public function current(): mixed
    {
        return $this->rows[$this->position];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function count(): int
    {
        return count($this->rows);
    }

    public function get($index)
    {
        return $this->rows[$index];
    }

    public function array()
    {
        $tmp = array();
        foreach ($this->rows as $row) {
            $tmp[] = $row->array();
        }
        return $tmp;
    }

    public function next(): void
    {
        $this->position++;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->rows[$this->position]);
    }

    public function add($row)
    {
        if ($row instanceof Row) {
            $this->rows[] = $row;
        } else {
            $this->rows[] = new Row($this->model, $row);
        }
    }

    final protected function use_where(&$model)
    {
        $primary_key = $model->primaryKey();
        $rows = $this->array();
        $keys = array();
        foreach ($rows as $row) {
            if (!isset($row[$primary_key])) {
                throw new Exception("Primary key not found");
            }

            $keys[] = $row[$primary_key];
        }
        $model->where($primary_key, 'IN', $keys);
    }

    public function delete()
    {
        if (count($this->rows) == 0) {
            return true;
        }

        $model = $this->model->new();
        $this->use_where($model);
        $delete = $model->delete();
        if (!!$delete) {
            $this->rows = array();
            $this->position = 0;
        }

        return $delete;
    }

    public function update($data = [])
    {
        if (count($this->rows) == 0) {
            return true;
        }

        $model = $this->model->new();
        $this->use_where($model);
        $update = $model->data($data)->update();
        if ($update === count($this->array())) {
            $clone = $this->array();
            foreach ($clone as $key => $row) {
                $this->rows[$key] = new Row($this->model, array_merge($row, $data));
            }
            $this->position = 0;
        }

        return $update;
    }
}