<?php

namespace Api\Auth\Model;

use Auth;
use Api\Database\Model;

class Roles extends Model
{
    const TB = 'auth_users_role';
    public function __construct()
    {
        parent::__construct(Auth::db(), self::TB);
    }
}
