<?php

namespace SkiddPH\Plugin\DB;

use Exception;

class Row
{
    protected $update = [];
    /**
     * Row constructor.
     * @param Model $model
     * @param array<string> $fields
     * @param array<string, mixed> $data
     */
    public function __construct(protected $model, protected readonly $fields, protected readonly $data)
    {
        
    }

    /**
     * Set a field value
     * @param string $name
     * @param mixed $value
     * @return void
     */
    final public function __set($name,  $value)
    {
        if (in_array($name, $this->fields)) {
            $this->update[$name] = $value;
        }
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
}
