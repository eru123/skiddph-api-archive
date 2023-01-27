<?php

namespace SkiddPH\Controller;

use Exception;
use SkiddPH\Model\User;
use SkiddPH\Model\UserInfo;
use SkiddPH\Model\UserRole;
use SkiddPH\Plugin\Auth\Email;
use SkiddPH\Plugin\DB\DB;
use SkiddPH\Core\HTTP\Request;
use SkiddPH\Plugin\Auth\JWT;
use SkiddPH\Plugin\Auth\Auth as Plugin;

class Auth
{
    static function signin($data = [])
    {
        $body = empty($data) ? Request::bodySchema([
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
        ]) : $data;

        $user = User::find('user', $body['user']) ?: User::find(UserInfo::getUserIdBy('email', $body['user']) ?? 0);

        if (!$user) {
            if (User::find(UserInfo::getUserIdBy('email', $body['user']) ?? 0)) {
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
    static function signup()
    {
        $body = Request::bodySchema([
            'user' => [
                'alias' => 'Username',
                'type' => 'string',
                'min' => 5,
                'max' => 24,
                'regex' => '/^[a-zA-Z0-9_]+$/',
                'required' => true,
            ],
            'pass' => [
                'alias' => 'Password',
                'type' => 'string',
                'min' => 8,
                'required' => true,
            ],
            'email' => [
                'alias' => 'Email',
                'type' => 'email',
                'required' => true,
            ],
            'fname' => [
                'alias' => 'First Name',
                'type' => 'string',
                'min' => 2,
                'required' => true,
            ],
            'lname' => [
                'alias' => 'Last Name',
                'type' => 'string',
                'min' => 2,
                'required' => true,
            ],
        ]);

        $body['hash'] = $body['pass'];
        unset($body['pass']);

        if (!pcfg('auth.allow_signup')) {
            throw new Exception('Signup is disabled', 403);
        }

        if (UserInfo::getUserIdBy('email', $body['email'])) {
            throw new Exception('Email already exists', 409);
        }

        if (!pcfg('auth.email_auto_verify')) {
            $body['pending_email'] = $body['email'];
            unset($body['email']);
        }

        if (User::find('user', $body['user'])) {
            throw new Exception('Username already exists', 409);
        }
        User::begin();
        try {
            $user = User::create($body)->save();
            if (pcfg('auth.email_must_verified') && !empty($body['pending_email'])) {
                $verify_id = static::emailSend([
                    'user_id' => $user->id,
                    'email' => $body['pending_email'],
                    'type' => 'new',
                    'user' => $user->user,
                    'name' => $body['fname'] . ' ' . $body['lname'],
                ])['verify_id'];
            }

            $to_info = ['email', 'pending_email', 'fname', 'lname'];
            $info = array_filter($body, function ($key) use ($to_info) {
                return in_array($key, $to_info);
            }, ARRAY_FILTER_USE_KEY);
            UserInfo::insertFor($user->id, $info);

            $roles = $user->id === 1 ? ['SUPERADMIN'] : [];
            UserRole::insertFor($user->id, $roles);

            $res = static::signin([
                'user' => $user->user,
                'pass' => $body['hash'],
                'remember' => 1,
            ]);

            if (isset($verify_id)) {
                $res['success'] = "Successfully created user. Please check your email for verification code.";
                $res['verify_id'] = $verify_id;
            } else {
                $res['success'] = "Successfully created user.";
            }

            User::commit();
            return $res;
        } catch (Exception $e) {
            User::rollback();
            $msg = $e->getMessage();
            $cde = (int) $e->getCode();
            if ($cde === 0) {
                $msg = 'Failed to create user';
            }
            throw new Exception($msg, $e->getCode());
        }
    }
    static function emailSend($data = [])
    {
        if (!empty($data)) {
            $user_id = $data['user_id'];
        } else {
            Plugin::guard();
            $user_id = Plugin::user()['id'];
        }

        $user = isset($data['user']) ? $data : User::find($user_id);
        $info = (isset($data['fname']) && isset($data['lname']) || isset($data['name'])) ? $data : UserInfo::from($user_id);

        $body = empty($data) ? Request::bodySchema([
            'email' => [
                'alias' => 'Email',
                'type' => 'email',
                'required' => true,
            ],
            'code' => [
                'alias' => 'Email verification type',
                'required' => true,
                'type' => 'enum',
                'values' => ['new', 'reset'],
                'default' => 'new',
            ],
        ]) : $data;

        $verify_id = Email::send([
            'user_id' => isset($user['id']) ? $user['id'] : $user['user_id'],
            'email' => $body['email'],
            'type' => isset($body['code']) ? $body['code'] : $body['type'],
            'user' => $user['user'],
            'name' => isset($info['name']) ? trim($info['name']) : trim($info['fname'] . ' ' . $info['lname']),
        ]);

        return [
            'success' => true,
            'verify_id' => $verify_id,
        ];
    }
}