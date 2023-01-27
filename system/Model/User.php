<?php

namespace SkiddPH\Model;

use SkiddPH\Helper\Date;
use SkiddPH\Plugin\DB\Model;
use SkiddPH\Plugin\DB\Row;
use SkiddPH\Plugin\Auth\Password;

class User extends Model
{
    protected $table = 'auth_users';

    public function set__hash($password)
    {
        return Password::hash($password);
    }

    public function verifyPassword($password, Row $row)
    {
        return Password::verify($password, $row->hash);
    }

    public function strip($row)
    {
        $data = $row->array();
        unset($data['hash']);
        unset($data['last_user']);
        unset($data['last_hash']);
        return new Row($this, $data);
    }

    public function insert__created_at()
    {
        return Date::parse('now', 'datetime');
    }

    public function insert__updated_at()
    {
        return Date::parse('now', 'datetime');
    }

    public function update__updated_at()
    {
        return Date::parse('now', 'datetime');
    }
}