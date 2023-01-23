<?php

namespace SkiddPH\Plugin\DB;

// Rows of Row
class Rows implements \Iterator
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

    public function current()
    {
        return $this->rows[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function count()
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
}
