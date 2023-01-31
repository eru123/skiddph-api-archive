<?php

namespace SkiddPH\Model;

use SkiddPH\Helper\Date;
use SkiddPH\Plugin\DB\Model;
use SkiddPH\Plugin\DB\Row;

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

    protected function f__inPending(int $user_id, string $email) {
        return !!$this->new()
            ->where('user_id', $user_id)
            ->where('email', $email)
            ->where('deleted_at', null)
            ->where('verified', false)
            ->first();
    }

    protected function update__verified(Row &$row) {
        $row->updated_at = Date::parse('now', 'datetime');
    }
}