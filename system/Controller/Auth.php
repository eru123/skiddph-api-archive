<?php

namespace SkiddPH\Controller;

use Exception;
use SkiddPH\Helper\Date;
use SkiddPH\Model\User;
use SkiddPH\Model\UserEmail;
use SkiddPH\Model\UserInfo;
use SkiddPH\Model\UserRole;
use SkiddPH\Plugin\Auth\Email;
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

        if (!($user = User::find('user', $body['user']))) {
            $email = UserEmail::where('email', $body['user'])
                ->where('deleted_at', null)
                ->where('verified', true)
                ->first();

            if (!$email) {
                throw new Exception('User not found', 404);
            }

            $user = User::find($email->user_id);
        }

        if (!$user) {
            throw new Exception('User not found', 404);
        }

        if (!$user->verifyPassword($body['pass'], $user)) {
            throw new Exception('Password is incorrect', 401);
        }

        $data = User::details($user->id);
        $jwt_fields = ['id', 'user', 'roles', 'emails'];

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
    static function createSignIn($id)
    {
        $user = User::find($id);
        if (!$user) {
            throw new Exception('User not found', 404);
        }

        $data = User::details($user->id);
        $jwt_fields = ['id', 'user', 'roles', 'emails'];

        $payload = array_filter($data, function ($key) use ($jwt_fields) {
            return in_array($key, $jwt_fields);
        }, ARRAY_FILTER_USE_KEY);

        $payload['iat'] = 'now';
        $payload['exp'] = 'now + 1 days';

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

        if (UserEmail::inUse($body['email'])) {
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
                UserEmail::insert([
                    'user_id' => $user->id,
                    'email' => $body['pending_email'],
                    'verified' => false,
                    'created_at' => Date::parse('now', 'datetime'),
                    'updated_at' => Date::parse('now', 'datetime'),
                ]);
                $verify_id = static::emailSend([
                    'user_id' => $user->id,
                    'email' => $body['pending_email'],
                    'type' => 'new',
                    'user' => $user->user,
                    'name' => $body['fname'] . ' ' . $body['lname'],
                ])['verify_id'];
            } else {
                UserEmail::insert([
                    'user_id' => $user->id,
                    'email' => $body['email'],
                    'verified' => true,
                    'created_at' => Date::parse('now', 'datetime'),
                    'updated_at' => Date::parse('now', 'datetime'),
                ]);
            }

            $to_info = ['fname', 'lname'];
            $info = array_filter($body, function ($key) use ($to_info) {
                return in_array($key, $to_info);
            }, ARRAY_FILTER_USE_KEY);

            if (!empty($info)) {
                UserInfo::upsertFor($user->id, $info);
            }

            $roles = $user->id === 1 ? ['SUPERADMIN'] : [];
            if (!empty($roles)) {
                UserRole::upsertFor($user->id, $roles);
            }

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
            throw new Exception($msg, $e->getCode(), $e);
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
        
        $body = empty($data) ? Request::bodySchema([
            'email' => [
                'alias' => 'Email',
                'type' => 'email',
                'required' => true,
            ],
            'type' => [
                'alias' => 'Email verification type',
                'required' => true,
                'type' => 'enum',
                'values' => ['new', 'reset'],
                'default' => 'new',
            ],
        ]) : $data;

        if (empty($data)) {
            $data = User::details($user_id);
        }

        $verify_id = Email::send([
            'user_id' => $user_id,
            'email' => $body['email'],
            'type' => $body['type'],
            'user' => $data['user'],
            'name' => isset($data['name']) ? trim($data['name']) : trim($data['fname'] . ' ' . $data['lname']),
        ]);

        return [
            'success' => true,
            'verify_id' => $verify_id,
        ];
    }
    static function emailVerify($data = [])
    {
        if (isset($data['emailToken'])) {
            $token = $data['emailToken'];
            $data = JWT::decode($token);
        }

        if (isset($data['user_id'])) {
            $user_id = $data['user_id'];
        } else {
            Plugin::guard();
            $user_id = Plugin::user()['id'];
        }

        $body = empty($data) ? Request::bodySchema([
            'code' => [
                'alias' => 'Verification Code',
                'type' => 'string',
                'required' => true,
            ],
            'type' => [
                'alias' => 'Verification Type',
                'type' => 'enum',
                'values' => ['new', 'reset'],
                'required' => true,
            ],
            'verify_id' => [
                'alias' => 'Verification ID',
                'type' => 'string',
                'required' => true,
            ],
        ]) : $data;

        try {
            UserInfo::begin();
            $res = Email::verify(['user_id' => $user_id, 'id' => $body['verify_id'], 'type' => $body['type'], 'code' => $body['code']]);
            UserInfo::commit();
            return array_merge($res ?? ['success' => true]);
        } catch (Exception $e) {
            UserInfo::rollback();

            if ($e->getCode() === 0) {
                throw new Exception('Failed to verify emailx', 500);
            }

            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}