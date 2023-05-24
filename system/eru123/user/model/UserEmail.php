<?php

namespace eru123\user\model;

use eru123\orm\Model;

class UserEmail extends Model
{   
    protected $name = 'UserEmail';
    protected $table = 'auth_users_email';
    protected $primary_key = 'id';
    protected $fields = [
        'user_id' => [
            'type' => 'integer',
            'null' => false,
        ],
        'email' => [
            'type' => 'varchar',
            'length' => 255,
            'null' => false,
        ],
        'verified' => [
            'type' => 'boolean',
            'default' => false,
            'null' => false,
        ],
        'created_at' => [
            'type' => 'datetime',
            'null' => true,
        ],
        'updated_at' => [
            'type' => 'datetime',
            'null' => true,
        ],
        'deleted_at' => [
            'type' => 'datetime',
            'null' => true,
        ],
    ];
}