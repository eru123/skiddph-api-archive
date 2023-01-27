<?php

namespace SkiddPH\Model;

use SkiddPH\Helper\Date;
use SkiddPH\Plugin\DB\Model;

class EmailVerification extends Model
{
    protected $table = 'auth_email_verification';

    protected function f__newCode($data): int
    {
        return $this->new()
            ->data([
                'user_id' => $data['user_id'],
                'updated_at' => Date::parse('now', 'datetime'),
                'code' => $data['code'],
                'type' => $data['type'],
                'email' => $data['email'],
            ])
            ->insert();
    }
}