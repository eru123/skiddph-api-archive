<?php

namespace Api\Database;

interface Model
{
    static function create(array $data);
    static function update(array $where, array $data);
    static function delete(array $where);
    static function get(array $where);
    static function all(array $where);
}
