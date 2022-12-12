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
        'HASH_METHOD' => [
            PASSWORD_BCRYPT,
            ['cost' => 12]
        ]
    ]
];
