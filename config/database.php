<?php

return [
    /**
     * Collection of Database Configurations.
     * Can be used as PDO Arguments.
     */
    'databases' => [
        /**
         * Default Database Configuration.
         */
        'default' => [
            /**
             * PDO DSN
             */
            e('DEFAULT_DB_DSN', 'mysql:host=localhost;dbname=skiddph'),
            /**
             * PDO Username
             */
            e('DEFAULT_DB_USER', 'root'),
            /**
             * PDO Password
             */
            e('DEFAULT_DB_PASS', ''),
            /**
             * PDO Options
             */
            NULL
        ]
        /**
         * Add more database configurations below by copying the default configuration.
         */
    ],
    /**
     * Database Environment to be use.
     */
    'database' => 'default'
];
