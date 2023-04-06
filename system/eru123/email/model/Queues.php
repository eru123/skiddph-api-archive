<?php

namespace eru123\email\model;

use eru123\orm\Model;

class Queues extends Model
{       
    protected $name = 'EmailQueues';
    protected $table = 'email_queues';
    protected $primary_key = 'id';
    protected $fields = [
        'to' => [
            'type' => 'text',
            'null' => true,
        ],
        'cc' => [
            'type' => 'text',
            'null' => true,
        ],
        'bcc' => [
            'type' => 'text',
            'null' => true,
        ],
        'subject' => [
            'type' => 'text',
            'null' => true,
        ],
        'body' => [
            'type' => 'text',
            'null' => true,
        ],
        'priority' => [
            'type' => 'integer',
            'null' => true,
        ],
        'for_approval' => [
            'type' => 'boolean',
            'null' => true,
        ],
        'approved_at' => [
            'type' => 'datetime',
            'null' => true,
        ],
        'pending_at' => [
            'type' => 'datetime',
            'null' => true,
        ],
        'delivered_at' => [
            'type' => 'datetime',
            'null' => true,
        ],
        'failed_at' => [
            'type' => 'datetime',
            'null' => true,
        ],
        'created_at' => [
            'type' => 'datetime',
            'null' => true,
        ],
        'deleted_at' => [
            'type' => 'datetime',
            'null' => true,
        ],
    ];

    public function queues()
    {   
        $sql = static::raw("SELECT id, to, cc, bcc, subject, body, priority, for_approval, approved_at, pending_at, delivered_at, failed_at, created_at, deleted_at FROM email_queues WHERE deleted_at IS NULL AND approved_at IS NOT NULL AND pending_at IS NULL AND delivered_at IS NULL AND failed_at IS NULL ORDER BY priority DESC, created_at ASC LIMIT 1");
        return $this->orm()->exec($sql);
    }
}