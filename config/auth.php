<?php

return [
    /**
     * Secret Key
     */
    'secret' => e('JWT_SECRET', 'default_secret_key'),
    /**
     * JWT Refresh Key
     */
    'refresh' => e('JWT_REFRESH', 'default_refresh_key'),
    /**
     * JWT Hash Algorithm
     */
    'alg' => 'HS256',
    /**
     * Password Hash Method - Arguments in `password_hash` function
     */
    'hash_method' => [
        /**
         * Password Hash Algorithm
         */
        PASSWORD_BCRYPT,
        /**
         * Password Hash Options
         */
        ['cost' => 12]
    ],
    /**
     * JWT Default Expirations - depends on login type
     */
    'token_expire_at' => [
        'short' => 'now + 30mins',
        'long' => 'now + 7days',
        'remember' => 'now + 30days'
    ],
    /**
     * Email verification token expiration
     */
    'email_verification_expire_at' => '24mins',
    /**
     * Must be verified to use services
     */
    'email_must_verified' => true,
    /**
     * Auto email verification
     */
    'email_auto_verify' => true,
    /**
     * Allow sign up
     */
    'allow_signup' => true,
    /**
     * Email allow resend if time less than or equal to specified time
     */
    'email_resend_if_time' => '5mins',
    /**
     * Email Verification Success URL
     */
    'email_verify_success_url' => pcfg('app.client').'/email-verify-success',
    /**
     * Email Verification Fail URL
     */
    'email_verify_fail_url' => pcfg('app.client').'/email-verify-fail'
];
