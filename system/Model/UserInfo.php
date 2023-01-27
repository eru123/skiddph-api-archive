<?php

namespace SkiddPH\Model;

use SkiddPH\Plugin\DB\Model;
use SkiddPH\Plugin\DB\DB;
use SkiddPH\Plugin\DB\Row;
use SkiddPH\Plugin\Auth\Password;

class UserInfo extends Model
{
    protected $table = 'auth_users_info';

    protected function f__getUserIdBy(string $by, string $email): Row|null
    {
        return $this->new()
            ->select('user_id')
            ->where('name', $by)
            ->where('value', json_encode($email))
            ->first();
    }

    protected function f__insertFor(int $user_id, $data): void
    {   
        $new_data = [];
        foreach ($data as $name => $value) {
            $new_data[] = [
                'user_id' => $user_id,
                'name' => $name,
                'value' => json_encode($value),
            ];
        }

        $this->new()
            ->data($new_data)
            ->insert();
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
            if (!isset($data[$name])) {
                $data[$name] = [];
            }

            $data[$name][] = $value;
        }

        foreach ($data as $name => $value) {
            if (count($value) == 1) {
                $data[$name] = $value[0];
            }
        }

        return $data;
    }

    protected function f__isValueExists(string $name, string $value): bool
    {
        return $this->new()
            ->where('name', $name)
            ->where('value', json_encode($value))
            ->count() > 0;
    }

    protected function f__removeFor(int $user_id, $data)
    {
        $del = $this->new()
            ->where('user_id', $user_id);

        foreach ($data as $name => $value) {
            $del->where('name', $name);
            if (is_array($value)) {
                $del->where('value', 'in', array_map(fn($v) => json_encode($v), $value));
            } else {
                $del->where('value', json_encode($value));
            }
        }

        return $del->delete();
    }
}