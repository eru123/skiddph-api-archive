<?php

namespace Api\Auth\Model;

use Auth;
use Api\Database\Model;

class Users extends Model
{
    const TB = 'auth_users';
    public function __construct()
    {
        parent::__construct(Auth::db(), self::TB);
    }

    public static function user(int $user_id)
    {
        $user = Auth::db()->table(self::TB)
            ->where([
                'id' => $user_id
            ])
            ->readOne()
            ->arr();
        return empty($user) ? null : $user;
    }

    public static function users(array $user_ids)
    {
        $users = Auth::db()->table(self::TB)
            ->where([
                'id' => [
                    'IN' => $user_ids
                ]
            ])
            ->readMany()
            ->arr();
        return empty($users) ? [] : $users;
    }
}