<?php

namespace SkiddPH\Plugin\DB;

use Exception;

class Row
{
    protected $update = [];
    protected $data = [];
    protected $model = null;

    /**
     * Row constructor.
     * @param Model $model
     * @param array<string> $fields
     * @param array<string, mixed> $data
     */
    final public function __construct($model, $data)
    {
        $this->model = $model;
        $this->data = $data;
    }

    /**
     * Set a field value
     * @param string $name
     * @param mixed $value
     * @return void
     */
    final public function __set($name, $value)
    {
        $this->update[$name] = $value;
    }

    /**
     * Get a field value
     * @param string $name
     * @throws Exception
     * @return mixed
     */
    final public function __get($name)
    {
        if (in_array($name, $this->fields)) {
            return $this->update[$name] ?? $this->data[$name];
        }

        throw new Exception("Field not found: $name");
    }

    final protected function use_where(&$model)
    {
        if ($model->primaryKey()) {
            $model->where($model->primaryKey(), $this->data[$model->primaryKey()]);
        } else {
            foreach ($this->data as $k => $v) {
                if (!$v instanceof Raw) {
                    $model->where($k, $v);
                }
            }
        }
    }

    /**
     * Update row
     * @param array<string, mixed> $data Optional data to update
     * @return static|false
     */
    final public function update($data = [])
    {
        $model = $this->model->new();
        foreach ($this->data as $k => $v) {
            $model->where($k, $v);
        }
        if (empty($this->update) || !$model->update(array_merge($this->update, $data))) {
            return false;
        }
        $this->data = array_merge($this->data, $this->update);
        $this->update = [];
        return $this;
    }

    /**
     * Delete row
     * @return bool
     */
    final public function delete()
    {
        $model = $this->model->new();
        foreach ($this->data as $k => $v) {
            $model->where($k, $v);
        }
        return !!$model->delete();
    }

    /**
     * Get row data
     * @return array<string, mixed>
     */
    final public function array()
    {
        return array_merge($this->data, $this->update);
    }

    /**
     * Get row data in JSON
     * @return string
     */
    final public function json()
    {
        return json_encode($this->array());
    }

    /**
     * Save Row
     * @param array<string, mixed> $data Optional data to update
     * @return static|false
     */
    final public function save($data = [])
    {
        $fields = $this->model->fields();
        $fillable = $this->model->fillable();

        $allowed = empty($fillable) ? $fields : $fillable;
        $allowed = array_fill_keys($allowed, true);

        $data = array_merge($this->update, $data);
        if (empty($this->update)) {
            return false;
        }

        foreach ($data as $k => $v) {
            if (!isset($allowed[$k])) {
                unset($data[$k]);
            }
        }

        $insert = $this->model->new()->insert($data);
        if (!$insert) {
            return false;
        }

        $model = $this->model->new();


        $this->data = $model->first()->array();
        $this->update = [];
        return $this;
    }
}