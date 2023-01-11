<?php

namespace SkiddPH\Plugin\Auth;

class Password
{
    /**
     * Hash a password.
     * @param   string  $password   The password to hash.
     * @return  string
     */
    final static function hash(string $password): string
    {
        return password_hash($password, ...pcfg('auth.hash_method', [PASSWORD_BCRYPT, ['cost' => 12]]));
    }

    /**
     * Verify a password against a hash.
     * @param   string  $password   The password to verify.
     * @param   string  $hash       The hash to verify against.
     * @return  bool
     */
    final static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}