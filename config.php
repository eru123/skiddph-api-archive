<?php

/**
 * Plugin Configuration
 * 
 * FORMAT:
 * 
 *     return [
 *         'PLUGIN_KEY' => <PLUGIN CONFIGURATION>
 *     ];
 */

return [
    // Database Plugin
    // Collection of Database configurations - PDO Arguments
    'DATABASES' => [
        'default' => [
            env('DEFAULT_DB_DSN'),
            env('DEFAULT_DB_USER'),
            env('DEFAULT_DB_PASS')
        ]
    ],

    // Auth Plugin
    'AUTHENTICATION' => [
        // Database Environment to be use in authentication
        'DB_ENV' => 'default',
        // JWT Secret Key
        'JWT_SECRET' => env('JWT_SECRET', 'default_secret_key'),
        // JWT Refresh Key
        'JWT_REFRESH' => env('JWT_REFRESH', 'default_refresh_key'),
        // JWT Hash Algorithm
        'JWT_ALG' => 'HS256',
        // Password Hash Method - Arguments in `password_hash` function
        'HASH_METHOD' => [
            PASSWORD_BCRYPT,
            ['cost' => 12]
        ],

        // JWT Default Expirations - depends on login type
        'TOKEN_EXPIRE_AT' => [
            'short' => 'now + 30mins',
            'long' => 'now + 7days',
            'remember' => 'now + 30days'
        ],

        // Email verification token expiration
        'EMAIL_VERIFICATION_EXPIRE_AT' => 'now + 24mins',
    ],

    // SMTP Plugin
    // Collection of SMTP configurations
    'SMTPS' => [
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

    // Google Drive Plugin (Test Only)
    "GOOGLE_DRIVE" => [
        "ID" => env('GOOGLE_CLIENT_ID'),
        "SECRET" => env('GOOGLE_CLIENT_SECRET'),
        "REDIRECT_URI" => env('GOOGLE_REDIRECT_URI'),
        "SCOPE" => "https://www.googleapis.com/auth/drive"
    ],

    // File Uploader Plugin
    'FILE_UPLOADER' => [

        // Database Environment to be use
        'DB_ENV' => 'default',

        // Max upload size
        'MAX_FILE_SIZE' => 2097152, // 1024 * 1024 * 2, // 2MB

        // Connector to be use
        // See `SkiddPH\Plugin\FileUploader\FileUploader::CONNECTORS`
        'CONNECTOR' => 's3bucket', // local|s3bucket

        // Upload Directory - For Local Storage Connector
        'UPLOAD_DIR' => __DIR__ . '/uploads',

        // S3 Bucket Configuration - For S3 Bucket Connector
        'S3_BUCKET' => [
            'key' => env('S3_KEY'),
            'secret' => env('S3_SECRET'),
            'region' => env('S3_REGION'),
            'version' => env('S3_VERSION'),
            'bucket' => env('S3_BUCKET'),
            'ttl' => '+20 minutes' // URL Expiration
        ]
    ]
];