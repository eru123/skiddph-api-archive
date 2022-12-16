<?php

namespace Api\Auth;

use Auth;
use Api\Database\Model;

class User implements Model
{
    const TB = 'auth_users';
    static function create(array $data)
    {
        return Auth::db()
            ->table(self::TB)
            ->data($data)
            ->insert();
    }

    static function update(array $where, array $data)
    {
        return Auth::db()
            ->table(self::TB)
            ->where($where)
            ->data($data)
            ->update();
    }

    static function delete(array $where)
    {
        return Auth::db()
            ->table(self::TB)
            ->where($where)
            ->delete();
    }

    static function get(array $where)
    {
        return Auth::db()
            ->table(self::TB)
            ->where($where)
            ->readOne();
    }

    static function all(array $where)
    {
        return Auth::db()
            ->table(self::TB)
            ->where($where)
            ->readMany();
    }
}
