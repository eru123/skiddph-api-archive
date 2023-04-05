<?php

namespace eru123\email\model;

use eru123\orm\Model;

class Attachments extends Model
{   
    protected $name = 'EmailAttachments';
    protected $table = 'email_attachments';
    protected $primary_key = 'id';
    protected $fields = [
        'email_id' => [
            'type' => 'int',
            'length' => 11,
            'null' => false,
        ],
        'path' => [
            'type' => 'text',
            'null' => true,
        ],
    ];
}