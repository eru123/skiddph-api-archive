<?php

namespace SkiddPH\Model;

use SkiddPH\Plugin\DB\Model;
use SkiddPH\Plugin\DB\DB;
use SkiddPH\Plugin\DB\Row;
use SkiddPH\Plugin\Auth\Password;

class UserRole extends Model
{
    protected $table = 'auth_users_info';

    protected function f__from(int $user_id): array
    {
        $user = $this->new()
            ->where('user_id', $user_id)
            ->get()
            ->array();

        $data = [];
        foreach ($user as $row) {
            $data[] = $row['role'];
        }

        return $data;
    }

}