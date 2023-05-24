<?php

namespace eru123\user\model;

use eru123\orm\Model;

class Users extends Model
{   
    protected $name = 'Users';
    protected $table = 'auth_users';
    protected $primary_key = 'id';
    protected $fields = [
        'user' => [
            'type' => 'varchar',
            'length' => 255,
            'null' => false,
        ],
        'hash' => [
            'type' => 'varchar',
            'length' => 255,
            'null' => false,
        ],
        'last_user' => [
            'type' => 'varchar',
            'length' => 255,
            'null' => true,
        ],
        'last_hash' => [
            'type' => 'varchar',
            'length' => 255,
            'null' => true,
        ],
        'created_at' => [
            'type' => 'datetime',
            'null' => true,
        ],
        'updated_at' => [
            'type' => 'datetime',
            'null' => true,
        ],
        'status' => [
            'type' => 'varchar',
            'length' => 255,
            'default' => 'active',
            'null' => false,
        ],
    ];

    protected $info_keys = null;

    public function searchInfo($user_id, $name, $value)
    {
        $this->info_keys = $this->getInfoKeys();

    }

    public function getInfoKeys()
    {
        if (empty($this->info_keys)) {
            $this->info_keys = [];
            $this->info_keys = $this->orm()->exec('SELECT DISTINCT name FROM auth_users_info')->toArray();
        }

        return $this->info_keys;
    }

    public function getUserInfoKeys($user_id)
    {
        $this->info_keys = $this->getInfoKeys();
    }
}