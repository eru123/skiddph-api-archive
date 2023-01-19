<?php

return [
    /**
     * Collection of SMTP configurations
     */
    'smtps' => [
        /**
         * Default SMTP Configuration
         */
        'default' => [
            /**
             * SMTP Host
             * Example: smtp.domain.com
             */
            'host' => e('SMTP_HOST'),
            /**
             * SMTP Port
             * Example: 587
             */
            'port' => e('SMTP_PORT'),
            /**
             * SMTP Username
             */
            'user' => e('SMTP_USER'),
            /**
             * SMTP Password
             */
            'pass' => e('SMTP_PASS'),
            /**
             * SMTP From Email
             * Example: example@domain.com
             */
            'from' => e('SMTP_FROM'),
            /**
             * SMTP From Name
             * Example: John Doe
             */
            'from_name' => e('SMTP_FROM_NAME'),
            /**
             * SMTP Debug
             * 0 = off (for production use)
             * 1 = client messages
             * 2 = client and server messages
             */
            'debug' => (int) e('SMTP_DEBUG', 0)
        ]
    ],
    /**
     * SMTP Environment to be use.
     */
    'smtp' => 'default'
];
