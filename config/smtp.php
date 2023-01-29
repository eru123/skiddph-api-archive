<?php
use PHPMailer\PHPMailer\PHPMailer;

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
             * SMTP Reply To Email
             */
            'reply_to' => e('SMTP_REPLY_TO', e('SMTP_FROM')),
            /**
             * SMTP Reply To Name
             */
            'reply_to_name' => e('SMTP_REPLY_TO_NAME', e('SMTP_FROM_NAME')),
            /**
             * SMTP Debug
             * 0 = off (for production use)
             * 1 = client messages
             * 2 = client and server messages
             */
            'debug' => (int) e('SMTP_DEBUG', 0),
            /**
             * SMTP Secure
             */
            'secure' => e('SMTP_HOST') === 'localhost' ? false : PHPMailer::ENCRYPTION_SMTPS,
            /**
             * SMTP Auth
             */
            'auth' => true,
            /**
             * SMTP Options
             */
            'options' => [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ],
            /**
             * SMTP Charset
             */
            'charset' => 'UTF-8',
            /**
             * SMTP Timeout
             */
            'timeout' => 10,
            /**
             * SMTP Keep Alive
             */
            'keep_alive' => false,
            /**
             * SMTP Priority
             * 1 = High
             * 3 = Normal
             * 5 = low
             * 7 = lowest
             * 9 = non-urgent
             * 10 = bulk
             * 12 = immediate
             */
            'priority' => 1,
        ]
    ],
    /**
     * SMTP Environment to be use.
     */
    'smtp' => 'default'
];