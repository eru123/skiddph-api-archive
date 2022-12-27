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

    final static function login($user, string $pass, array $payload = [], array $opts = [], array $payload_keys = [])
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
            throw new Exception('Invalid credentials', 401);
        }

        if (!Password::verify($pass, $user['hash'])) {
            throw new Exception('Invalid credentials', 401);
        }

        try {
            $user = Users::find($user['id'], true, true);
        } catch (Exception $e) {
            throw new Error('Failed to login', 500);
        }

        $payload_keys_default = ['id', 'user', 'roles'];
        $payload_keys = array_merge($payload_keys_default, $payload_keys);

        $expires_at = @self::config()->get('TOKEN_EXPIRE_AT')[$opts['ttl']] ?? 'now + 7days';

        $pre_payload = Arr::from($user)->pick($payload_keys)->merge([
            'iat' => 'now',
            'exp' => $expires_at,
        ])->arr();

        $payload = array_merge($pre_payload, $payload);
        $token = JWT::encode($payload);

        return [
            "success" => true,
            'data' => $user,
            'token' => $token,
            'refresh_token' => JWT::issue_refresh($token),
        ];
    }

    final static function refreshToken(string $token, string $refresh_token)
    {
        $token = JWT::refresh($token, $refresh_token);

        return [
            'success' => true,
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
        return Users::create($to_insert);
    }

    final static function getBearerToken()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        if (!$authHeader) {
            return null;
        }
        $token = explode(' ', $authHeader);
        if (count($token) !== 2) {
            return null;
        }
        return $token[1];
    }

    final static function user()
    {
        $user = Config::get('USER');
        if (!empty($user)) {
            return $user;
        }

        $token = self::getBearerToken();
        if (!$token) {
            return null;
        }

        $user = JWT::decode($token);

        if (empty($user)) {
            return null;
        }

        Config::set('USER', $user);
        return $user;
    }

    final static function accessControl($allowed_roles = [])
    {
        $user = self::user();
        if (empty($user) || empty($user['roles'])) {
            throw new Exception('Unauthorized', 401);
        }

        $allowed = RolesModel::parse_roles($allowed_roles);
        $matched = array_intersect($user['roles'], $allowed);

        if (empty($matched)) {
            throw new Exception('Forbidden', 403);
        }

        return true;
    }

    final static function guard()
    {
        $user = self::user();
        if (empty($user)) {
            throw new Exception('Unauthorized', 401);
        }
    }
}