<?php

return [
    /**
     * Collection of Database Configurations.
     * Can be used as PDO Arguments.
     */
    'databases' => [
        'default' => [
            e('DEFAULT_DB_DSN', 'mysql:host=localhost;dbname=skiddph'),
            e('DEFAULT_DB_USER', 'root'),
            e('DEFAULT_DB_PASS', '')
        ]
    ],
    /**
     * Database Environment to be use.
     */
    'database' => 'default'
];
