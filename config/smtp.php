<?php

return [
    /**
     * Collection of SMTP configurations
     */
    'smtps' => [
        'default' => [
            'host' => env('SMTP_HOST'),
            'port' => env('SMTP_PORT'),
            'user' => env('SMTP_USER'),
            'pass' => env('SMTP_PASS'),
            'from' => env('SMTP_FROM'),
            'from_name' => env('SMTP_FROM_NAME'),
            'debug' => 0
        ]
    ],
    /**
     * SMTP Environment to be use.
     */
    'smtp' => 'default'
];
