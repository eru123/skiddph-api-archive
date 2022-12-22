<?php

use Api\Database\ORM;
use Api\Lib\Arr;
use Api\Auth\{
    JWT,
    Password,
    Users
};

use Api\Auth\Model\{
    Users as UsersModel,
    Roles as RolesModel,
    Info as InfoModel
};

class Auth implements PluginDB, PluginKey
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

    final static function db(): ORM
    {
        return Database::connect(self::config()->get('DB_ENV'));
    }

    final static function login(string $user, string $pass, array $opts = [], array $payload_keys = [])
    {
        $opts_default = [
            'ttl' => 'long',
        ];

        $opts = array_merge($opts_default, $opts);

        $orm = self::db();
        $user = Users::find(is_numeric($user) ? $user : [
            'user' => $orm->quote($user),
        ], false, false);


        if (empty($user)) {
            return null;
        }

        if (!Password::verify($pass, $user['hash'])) {
            return null;
        }

        $user = Users::find(['id' => $user['id']]);

        $payload_keys_default = ['id', 'user', 'roles'];
        $payload_keys = array_merge($payload_keys_default, $payload_keys);

        $expires_at = @self::config()->get('TOKEN_EXPIRE_AT')[$opts['ttl']] ?? 'now + 7days';

        $payload = Arr::from($user)->pick($payload_keys)->merge([
            'iat' => 'now',
            'exp' => $expires_at,
        ])->arr();

        $token = JWT::encode($payload);

        return [
            'data' => $user,
            'token' => $token,
            'refresh_token' => JWT::issue_refresh($token),
        ];
    }

    final static function refreshToken(string $token, string $refresh_token)
    {
        $token = JWT::refresh($token, $refresh_token);

        return [
            'token' => $token,
            'refresh_token' => JWT::issue_refresh($token),
        ];
    }

    final static function register(string $user, string $pass, $roles = [], array $data = [])
    {
        $to_insert = [
            'user' => $user,
            'pass' => $pass,
            'roles' => $roles,
        ];
        $to_insert = array_merge($data, $to_insert);
        Users::create($to_insert);
        return true;
    }
}
