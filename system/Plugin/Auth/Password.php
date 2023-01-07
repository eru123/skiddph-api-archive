<?php

namespace SkiddPH\Plugin\Auth;

class Password
{
    /**
     * Get Password hash method
     * @return array
     */
    final static function getHashMethod(): array
    {
        $cfg = Auth::config();
        $default_opts = [PASSWORD_BCRYPT, ['cost' => 12]];
        return $cfg->has('HASH_METHOD') ? $cfg->get('HASH_METHOD') : $default_opts;
    }

    /**
     * Hash a password.
     * @param   string  $password   The password to hash.
     * @return  string
     */
    final static function hash(string $password): string
    {
        return password_hash($password, ...self::getHashMethod());
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
