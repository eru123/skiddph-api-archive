<?php

namespace SkiddPH\Model;

use SkiddPH\Plugin\DB\Model;
use SkiddPH\Plugin\DB\DB;
use SkiddPH\Plugin\DB\Row;
use SkiddPH\Plugin\Auth\Password;

class UserInfo extends Model
{
    protected $table = 'auth_users_info';

    protected function f__getUserIdByEmail(string $email): Row
    {
        return $this->new()
            ->select('user_id')
            ->where('email', $email)
            ->first();
    }

    protected function f__from(int $user_id): array
    {
        $user = $this->new()
            ->where('user_id', $user_id)
            ->get()
            ->array();

        $data = [];
        foreach ($user as $row) {
            $name = $row['name'];
            $value = json_decode($row['value'], true);
            if (!$data[$name]) {
                $data[$name] = [];
            }

            $data[$name][] = $value;
        }

        foreach($data as $name => $value) {
            if (count($value) == 1) {
                $data[$name] = $value[0];
            }
        }

        return $data;
    }

}