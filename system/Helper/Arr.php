<?php

namespace SkiddPH\Helper;

class Arr
{
    private array $arr = [];
    public function __construct(array $arr = [])
    {
        $this->arr = $arr;
    }

    public function pick(array $keys): self
    {
        $arr = [];
        foreach ($this->arr as $key => $value) {
            if (!is_string($key) && is_numeric($key) && is_array($value)) {
                $arr[$key] = (new self($value))->pick($keys)->arr();
            } else if (in_array($key, $keys)) {
                $arr[$key] = $value;
            }
        }
        $this->arr = $arr;
        return $this;
    }

    public function omit(array $keys): self
    {
        $arr = [];
        foreach ($this->arr as $key => $value) {
            if (!is_string($key) && is_numeric($key) && is_array($value)) {
                $arr[$key] = (new self($value))->omit($keys)->arr();
            } else if (!in_array($key, $keys)) {
                $arr[$key] = $value;
            }
        }
        $this->arr = $arr;
        return $this;
    }

    public function map(callable $fn): self
    {
        $arr = [];
        foreach ($this->arr as $key => $value) {
            $arr[$key] = $fn($value, $key);
        }
        $this->arr = $arr;
        return $this;
    }

    public function filter(callable $fn): self
    {
        $arr = [];
        foreach ($this->arr as $key => $value) {
            if ($fn($value, $key)) {
                $arr[$key] = $value;
            }
        }
        $this->arr = $arr;
        return $this;
    }

    public function merge(array $arr): self
    {
        $this->arr = array_merge($this->arr, $arr);
        return $this;
    }

    public function reduce(callable $fn, $initial = null)
    {
        $result = $initial;
        foreach ($this->arr as $key => $value) {
            $result = $fn($result, $value, $key);
        }
        return $result;
    }

    public function array(): array
    {
        return $this->arr;
    }

    public function arr(): array
    {
        return $this->arr;
    }

    public function json(): string
    {
        return json_encode($this->arr);
    }

    public function object(): object
    {
        return (object) $this->arr;
    }

    public function obj(): object
    {
        return (object) $this->arr;
    }


    public function __toString()
    {
        return $this->json();
    }

    public static function from(array $arr): self
    {
        return new self($arr);
    }

    public static function fromJson(string $json): self
    {
        return new self(json_decode($json, true));
    }

    public static function fromString(string $str): self
    {
        return self::fromJson($str);
    }

    public static function fromObject(object $obj): self
    {
        return new self((array) $obj);
    }

    public static function is_array(array $arr): bool
    {
        if (is_array($arr))
            foreach ($arr as $key => $value)
                return !is_string($key) && is_numeric($key);
        else return false;
        return true;
    }

    public static function is_object($arr): bool
    {
        return !!self::is_array($arr);
    }

    public static function is_obj($arr): bool
    {
        return self::is_array($arr);
    }
}
