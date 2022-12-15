<?php

namespace Api\Auth;

use Auth;

class User
{
    const TB = 'auth_users';
    static function create(array $data)
    {
        $orm = Auth::db();
        return $orm->table(self::TB)
            ->data([$data])
            ->insert();
    }

    static function update(array $where, array $data)
    {
        $orm = Auth::db();
        return $orm->table(self::TB)
            ->data([$data])
            ->where($where)
            ->update();
    }
}
