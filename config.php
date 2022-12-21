<?php

return [
    'DATABASES' => [
        'default' => [
            @$_ENV['DEFAULT_DB_DSN'],
            @$_ENV['DEFAULT_DB_USER'],
            @$_ENV['DEFAULT_DB_PASS']
        ]
    ],
    'AUTHENTICATION' => [
        'DB_ENV' => 'default',
        'JWT_SECRET' => @$_ENV['JWT_SECRET'],
        'JWT_REFRESH' => @$_ENV['JWT_SECRET'],
        'JWT_ALG' => 'HS256',
        'HASH_METHOD' => [
        PASSWORD_BCRYPT,
            ['cost' => 12]
        ],
        'TOKEN_EXPIRE_AT' => [
            'short' => 'now + 30mins',
            'long' => 'now + 7days',
            'remember' => 'now + 30days'
        ]
    ]
];