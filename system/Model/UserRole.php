<?php

namespace SkiddPH\Model;

use SkiddPH\Plugin\DB\Model;
use SkiddPH\Plugin\DB\DB;
use SkiddPH\Plugin\DB\Row;
use SkiddPH\Plugin\Auth\Password;

class UserRole extends Model
{
    protected $table = 'auth_users_role';

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

    protected function f__insertFor(int $user_id, $data): void
    {
        $new_data = [];
        foreach ($data as $value) {
            $new_data[] = [
                'user_id' => $user_id,
                'role' => strtoupper((string) $value),
            ];
        }

        $this->new()
            ->data($new_data)
            ->insert();
    }

}