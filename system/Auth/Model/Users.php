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
}
