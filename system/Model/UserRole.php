<?php

namespace SkiddPH\Model;

use SkiddPH\Helper\Date;
use SkiddPH\Plugin\DB\Model;

class UserRole extends Model
{
    protected $table = 'auth_users_role';

    protected function f__upsertFor(int $user_id, array $data): void
    {   
        if (empty($data)) {
            return;
        }

        $data = array_map('trim', $data);
        $data = array_map('strtoupper', $data);
        $data = array_unique($data);

        $new_data = [];
        foreach ($data as $value) {
            $new_data[] = [
                'user_id' => $user_id,
                'role' => $value,
                'created_at' => Date::parse('now', 'datetime')
            ];
        }

        if (empty($new_data)) {
            return;
        }

        $this->new()
            ->data($new_data)
            ->upsert();
    }

    protected function f__deleteFor(int $user_id, array $data): void
    {   
        if (empty($data)) {
            return;
        }
        
        $data = array_map('trim', $data);
        $data = array_map('strtoupper', $data);
        $data = array_unique($data);

        $model = $this->new();
        $model->where('user_id', $user_id);
        foreach ($data as $i => $value) {
            if ($i === 0) {
                $model->where('role', $value);
            } else {
                $model->orWhere('role', $value);
            }
        }

        $model->delete();
    }
}