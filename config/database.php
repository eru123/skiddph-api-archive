<?php

return [
    /**
     * Collection of Database Configurations.
     * Can be used as PDO Arguments.
     */
    'databases' => [
        'default' => [
            env('DEFAULT_DB_DSN', 'mysql:host=localhost;dbname=skiddph'),
            env('DEFAULT_DB_USER', 'root'),
            env('DEFAULT_DB_PASS', '')
        ]
    ],
    /**
     * Database Environment to be use.
     */
    'database' => 'default'
];
