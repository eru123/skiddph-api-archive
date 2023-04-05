<?php

namespace eru123\email\model;

use eru123\orm\Model;

class Logs extends Model
{   
    protected $name = 'EmailLogs';
    protected $table = 'email_logs';
    protected $primary_key = 'id';
    protected $fields = [
        'email_id' => [
            'type' => 'int',
            'length' => 11,
            'null' => false,
        ],
        'message' => [
            'type' => 'text',
            'null' => true,
        ],
        'created_at' => [
            'type' => 'datetime',
            'null' => true,
        ],
    ];
}