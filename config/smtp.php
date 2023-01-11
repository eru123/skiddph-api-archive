<?php

return [
    /**
     * Collection of SMTP configurations
     */
    'smtps' => [
        'default' => [
            'host' => e('SMTP_HOST'),
            'port' => e('SMTP_PORT'),
            'user' => e('SMTP_USER'),
            'pass' => e('SMTP_PASS'),
            'from' => e('SMTP_FROM'),
            'from_name' => e('SMTP_FROM_NAME'),
            'debug' => 0
        ]
    ],
    /**
     * SMTP Environment to be use.
     */
    'smtp' => 'default'
];
