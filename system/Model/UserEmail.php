<?php

namespace SkiddPH\Model;

use SkiddPH\Helper\Date;
use SkiddPH\Plugin\DB\Model;

class UserEmail extends Model
{
    protected $table = 'auth_users_email';
    protected function f__inUse(string $email) {
        return !!$this->new()
            ->where('email', $email)
            ->where('deleted_at', null)
            ->where('verified', true)
            ->first();
    }
}