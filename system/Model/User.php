<?php

namespace SkiddPH\Model;

use SkiddPH\Plugin\DB\Model;

class User extends Model
{
    protected $table = 'auth_users';
    protected $primary_key = 'id';
}