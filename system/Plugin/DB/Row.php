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
    final public function __construct(Model $model, array $data = [], array $update = [])
    {
        $this->model = $model;
        $this->data = $data;
        $this->update = $update;
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
        if (in_array($name, $this->model->fields())) {
            return $this->update[$name] ?? ($this->data[$name] ?? null);
        }

        throw new Exception("Field not found: $name");
    }

    final public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $arguments);
        } else if (method_exists($this->model, $name)) {
            $arguments[] = $this;
            return call_user_func_array([$this->model, $name], $arguments);
        }

        throw new Exception("Method not found: $name");
    }

    final protected function use_where(&$model)
    {
        $data = $this->filtered_data();
        $primary_key = $model->primaryKey();

        if ($primary_key) {
            $model->where($primary_key, $data[$primary_key]);
        } else {
            foreach ($this->data as $k => $v) {
                if (!$v instanceof Raw) {
                    $model->where($k, $v);
                }
            }
        }
    }

    final protected function filtered_data()
    {
        $fields = $this->model->fields();
        $fillable = $this->model->fillable();

        $allowed = empty($fillable) ? $fields : $fillable;
        $allowed = array_fill_keys($allowed, true);

        $data = $this->data;
        foreach ($data as $k => $v) {
            if (!isset($allowed[$k])) {
                unset($data[$k]);
            }
        }

        return $data;
    }
    final protected function to_insert_data()
    {
        $fields = $this->model->fields();
        $fillable = $this->model->fillable();

        $allowed = empty($fillable) ? $fields : $fillable;
        $allowed = array_fill_keys($allowed, true);

        $data = array_merge($this->data, $this->update);
        $cast = $this->model->cast($this->model->get_prefix_set_cast(), $data);
        $insert = $this->model->cast($this->model->get_prefix_insert_cast(), array_fill_keys($fields, null));

        foreach ($data as $k => $v) {
            if (!isset($allowed[$k])) {
                unset($data[$k]);
            }
        }

        return array_merge($data, $cast, $insert);
    }
    final protected function set_primary_key($value)
    {
        $primary_key = $this->model->primaryKey();
        if ($primary_key) {
            $this->data[$primary_key] = $value;
        }
        return $this;
    }

    /**
     * Update row
     * @param array<string, mixed> $data Optional data to update
     * @return static|false
     */
    final public function update($data = [])
    {
        $model = $this->model->new();
        $fields = $this->model->fields();
        $fields_data = array_fill_keys($fields, null);
        $this->use_where($model);
        $update = $this->model->cast($this->model->get_prefix_update_cast(), $fields_data);
        $data = array_merge($update, $this->update, $data);
        if (empty($this->update) || !$model->update($data)) {
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
        $this->use_where($model);
        if (!!$model->delete()) {
            $this->data = [];
            $this->update = [];
            return true;
        }
        return false;
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
        $this->update = array_merge($this->update, $data);
        $data = $this->to_insert_data();
        if (empty($data)) {
            return false;
        }
        
        $insert = $this->model->new()->data($data)->insert();

        $this->set_primary_key($insert);
        if (!$insert) {
            return false;
        }
        
        $model = $this->model->new();
        $this->use_where($model);
        $this->data = $model->first()->array();
        $this->update = [];
        return $this;
    }
}