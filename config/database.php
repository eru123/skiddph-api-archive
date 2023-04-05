<?php

// use SkiddPH\Plugin\DB\Helper;

return [
    /**
     * Collection of Database Configurations.
     * Can be used as PDO Arguments.
     */
    'databases' => [
        /**
         * Default Database Configuration.
         *  - uses PDO Arguments
         */
        'default' => [
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
        ],
        /**
         * Production Database Configuration
         * - uses PDO Arguments
         * - uses DATABASE_URL environment variable from Heroku 
         */
        // 'production' => Helper::buildPdoArgs(e('DATABASE_URL'))
        'production' => [
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
        ],
    ],
    /**
     * Database Environment to be use.
     */
    'database' => e('DB_ENV') === 'development' ? 'default' : 'production'
];