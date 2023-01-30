<?php

namespace SkiddPH\Model;

use SkiddPH\Helper\Date;
use SkiddPH\Plugin\DB\Model;
use SkiddPH\Plugin\DB\DB;
use SkiddPH\Plugin\DB\Row;
use SkiddPH\Plugin\Auth\Password;

class UserInfo extends Model
{
    protected $table = 'auth_users_info';

    protected function f__findBy(string $by, string $email): Row|null
    {
        return $this->new()
            ->select('user_id')
            ->where('name', $by)
            ->where('value', json_encode($email))
            ->first();
    }

    protected function f__upsertFor(int $user_id, array $data)
    {   
        if(empty($data)) {
            return;
        }

        $insert = [];
        foreach ($data as $key => $value) {
            $insert[] = [
                'user_id' => $user_id,
                'name' => $key,
                'value' => json_encode($value),
                'created_at' => Date::parse('now', 'datetime'),
                'updated_at' => Date::parse('now', 'datetime')
            ];
        }

        $this->new()
            ->data($insert)
            ->upsert();
    }

    protected function f__deleteFor(int $user_id, array $data)
    {   
        if (empty($data)) {
            return;
        }
        
        $model = $this->new();
        $model->where('user_id', $user_id);
        foreach ($data as $i => $value) {
            if ($i === 0) {
                $model->where('name', $value);
            } else {
                $model->orWhere('name', $value);
            }
        }

        $model->delete();
    }
}