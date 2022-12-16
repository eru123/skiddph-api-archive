<?php

namespace Api\Auth\Model;

use Auth;
use Api\Database\Model;

class Info extends Model
{
    const TB = 'auth_users_info';
    public function __construct()
    {
        parent::__construct(Auth::db(), self::TB);
    }
}
