<?php

namespace eru123\user\model;

use eru123\orm\Model;

class UserEmailOtp extends Model
{   
    protected $name = 'UserEmailOtp';
    protected $table = 'auth_email_otp';
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
        'code' => [
            'type' => 'varchar',
            'length' => 255,
            'null' => false,
        ],
        'hash' => [
            'type' => 'varchar',
            'length' => 255,
            'null' => false,
        ],
        'callback' => [
            'type' => 'text',
            'null' => true,
        ],
        'used' => [
            'type' => 'boolean',
            'default' => false,
            'null' => false,
        ],
        'created_at' => [
            'type' => 'datetime',
            'null' => false,
        ],
        'updated_at' => [
            'type' => 'datetime',
            'null' => false,
        ],
        'deleted_at' => [
            'type' => 'datetime',
            'null' => true,
        ],
    ];
}