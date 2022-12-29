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
        'JWT_REFRESH' => @$_ENV['JWT_REFRESH'],
        'JWT_ALG' => 'HS256',
        'HASH_METHOD' => [
            PASSWORD_BCRYPT,
            ['cost' => 12]
        ],
        'TOKEN_EXPIRE_AT' => [
            'short' => 'now + 30mins',
            'long' => 'now + 7days',
            'remember' => 'now + 30days'
        ],
        'EMAIL_VERIFICATION_EXPIRE_AT' => 'now + 24mins',
    ],
    'SMTPS' => [
        'default' => [
            'host' => @$_ENV['SMTP_HOST'],
            'port' => @$_ENV['SMTP_PORT'],
            'user' => @$_ENV['SMTP_USER'],
            'pass' => @$_ENV['SMTP_PASS'],
            'from' => @$_ENV['SMTP_FROM'],
            'from_name' => @$_ENV['SMTP_FROM_NAME'],
            'debug' => 0
        ]
    ],
    "GOOGLE_DRIVE" => [
        "ID" => @$_ENV['GOOGLE_CLIENT_ID'],
        "SECRET" => @$_ENV['GOOGLE_CLIENT_SECRET'],
        "REDIRECT_URI" => @$_ENV['GOOGLE_REDIRECT_URI'],
        "SCOPE" => "https://www.googleapis.com/auth/drive"
    ]
];
