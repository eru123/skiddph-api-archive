<?php

namespace Api\Authentication;

use Api\Core\{
    Plugin,
    PluginConfig
};
use Api\Database\{
    Database,
    ORM
};

class Auth extends Plugin
{
    private static $key = "AUTHENTICATION";

    static function key(string $key = null): string
    {
        if (is_string($key) && !empty($key)) {
            self::$key = $key;
        }

        return self::$key;
    }

    static function config(): PluginConfig
    {
        return new PluginConfig(self::$key);
    }

    /**
     * Returns the database ORM instance
     * @return ORM
     */
    final static function db(): ORM
    {
        $cfg = self::config();
        return Database::connect($cfg->get('DB_ENV'));
    }

    /**
     * Get Password hash method
     * @return array
     */
    final static function getHashMethod(): array
    {
        $cfg = self::config();
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
     * Verify a password hash.
     * @param   string  $password   The password to verify.
     * @param   string  $hash       The hash to verify against.
     * @return  bool
     */
    final static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
