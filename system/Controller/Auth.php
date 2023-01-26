<?php

namespace SkiddPH\Controller;

use Exception;
use SkiddPH\Model\User;
use SkiddPH\Model\UserInfo;
use SkiddPH\Model\UserRole;
use SkiddPH\Plugin\DB\DB;
use SkiddPH\Core\HTTP\Request;
use SkiddPH\Plugin\Auth\JWT;
use SkiddPH\Plugin\Auth\Auth as Plugin;

class Auth
{
    static function signin()
    {
        $body = Request::bodySchema([
            'user' => [
                'alias' => 'Username or Email',
                'type' => 'string',
                'min' => 5,
                'max' => 24,
                'required' => true,
            ],
            'pass' => [
                'alias' => 'Password',
                'type' => 'string',
                'min' => 8,
                'required' => true,
            ],
            'remember' => [
                'alias' => 'Remember Me',
                'type' => 'enum',
                'values' => [1, 7, 30],
                'default' => 1,
            ],
        ]);

        $user = User::find('user', $body['user']) ?: User::find(UserInfo::getUserIdBy('email', $body['user']) ?? 0);

        if (!$user) {
            if(User::find(UserInfo::getUserIdBy('email', $body['user']) ?? 0)) {
                throw new Exception('Email must be verified to use in login', 401);
            }

            throw new Exception('User not found', 404);
        }

        if (!$user->verifyPassword($body['pass'], $user)) {
            throw new Exception('Password is incorrect', 401);
        }

        $info = UserInfo::from($user->id);
        $roles = UserRole::from($user->id);

        $data = array_merge($user->array(), $info, ['roles' => $roles]);
        $jwt_fields = ['id', 'user', 'roles', 'email'];

        $payload = array_filter($data, function ($key) use ($jwt_fields) {
            return in_array($key, $jwt_fields);
        }, ARRAY_FILTER_USE_KEY);

        $payload['iat'] = 'now';
        $payload['exp'] = 'now + ' . $body['remember'] . ' days';

        $token = JWT::encode($payload);
        $refresh = JWT::issue_refresh($token);

        return [
            "success" => true,
            'data' => $data,
            'token' => $token,
            'refresh_token' => $refresh,
        ];
    }
}