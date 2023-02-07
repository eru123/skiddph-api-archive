<?php

namespace SkiddPH\Controller;

use Exception;
use SkiddPH\Core\HTTP\Response;
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
    static function createSignIn(int $id)
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
                $verify_id = static::emailSend([
                    'user_id' => $user->id,
                    'email' => $body['pending_email'],
                    'type' => 'new',
                    'user' => $user->user,
                    'name' => $body['fname'] . ' ' . $body['lname'],
                ])['verify_id'];

                UserEmail::insert([
                    'user_id' => $user->id,
                    'email' => $body['pending_email'],
                    'verified' => false,
                    'created_at' => Date::parse('now', 'datetime'),
                    'updated_at' => Date::parse('now', 'datetime'),
                ]);
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
        $used_token = false;

        if (isset($data['emailToken'])) {
            $token = $data['emailToken'];
            $data = JWT::decode($token);
            $used_token = true;
        }

        if (isset($data['user_id'])) {
            $user_id = $data['user_id'];
        } else {
            Plugin::guard();
            $user_id = Plugin::user()['id'];
        }

        $success_url = pcfg('auth.email_verify_success_url');
        $fail_url = pcfg('auth.email_verify_fail_url');

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
            $verified = Email::verify([
                'verify_id' => $body['verify_id'],
                'user_id' => $user_id,
                'id' => $body['verify_id'],
                'type' => $body['type'],
                'code' => $body['code']
            ]);

            $res = null;

            if (!$verified) {
                throw new Exception('Invalid verification code', 400);
            }

            if ($body['type'] === 'new') {
                $res = static::createSignIn($user_id);
            }

            UserInfo::commit();

            if ($used_token) {
                return Response::redirect($success_url);
            }

            return array_merge($res ?? ['success' => true]);
        } catch (Exception $e) {
            UserInfo::rollback();

            if ($used_token) {
                return Response::redirect($success_url);
            }

            if ($e->getCode() === 0) {
                throw new Exception('Failed to verify email', 500, $e);
            }

            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
    static function addEmail()
    {
        Plugin::guard();
        $user_id = Plugin::user()['id'];
        $body = Request::bodySchema([
            'email' => [
                'alias' => 'Email',
                'type' => 'string',
                'regex' => '/^[\w.+-]+@[\w]+\.[\w.]+$/',
                'required' => true,
            ],
        ]);

        $email = $body['email'];
        if (UserEmail::inUse($email)) {
            throw new Exception('Email already in use', 400);
        }

        try {
            UserEmail::begin();
            $verify_id = static::emailSend([
                'user_id' => $user_id,
                'email' => $email,
                'type' => 'new',
                'fname' => (string) @Plugin::user()['fname'],
                'lname' => (string) @Plugin::user()['lname'],
                'user' => (string) @Plugin::user()['user'],
            ])['verify_id'];

            UserEmail::insert([
                'user_id' => $user_id,
                'email' => $email,
                'verified' => false,
                'created_at' => Date::parse('now', 'datetime'),
                'updated_at' => Date::parse('now', 'datetime'),
            ]);

            UserEmail::commit();
            return [
                'success' => true,
                'verify_id' => $verify_id,
            ];
        } catch (Exception $e) {
            UserEmail::rollback();

            if ($e->getCode() === 0) {
                throw new Exception('Failed to add email', 500, $e);
            }

            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
    static function removeEmail()
    {
        Plugin::guard();
        $user_id = Plugin::user()['id'];
        $primary_email = Plugin::user()['primary_email'];

        $body = Request::bodySchema([
            'email' => [
                'alias' => 'Email',
                'type' => 'string',
                'regex' => '/^[\w.+-]+@[\w]+\.[\w.]+$/',
                'required' => true,
            ],
            'emails' => [
                'alias' => 'Emails',
                'type' => 'array',
                'required' => false,
            ],
        ]);

        if (isset($body['emails']) && isset($body['email'])) {
            throw new Exception('Cannot specify both emails and email', 400);
        }

        if (isset($body['emails'])) {
            $emails = $body['emails'];
        } else {
            $emails = [$body['email']];
        }

        $emails = array_map(function ($email) {
            return trim($email);
        }, $emails);

        $emails = array_filter($emails, function ($email) {
            return !empty($email);
        });

        if (empty($emails)) {
            throw new Exception('No emails specified', 400);
        }

        $emails = array_unique($emails);
        if ($primary_email && in_array($primary_email, $emails)) {
            throw new Exception('Cannot remove primary email', 400);
        }

        $deleteVerified = UserEmail::safeDelete($user_id, $emails, true);
        $deleteUnverified = UserEmail::safeDelete($user_id, $emails, false);

        $total = $deleteVerified + $deleteUnverified;

        if ($total === 0) {
            throw new Exception('No emails removed', 400);
        }

        $res = $deleteVerified ? static::createSignIn($user_id) : [];
        $res['success'] = true;
        $res['deleted'] = $total;

        return $res;
    }

    static function user($p)
    {
        Plugin::guard();
        $user_id = Plugin::user()['id'];

        if (isset($p['userId'])) {
            Plugin::accessControl('SUPERADMIN,VIEWUSER,MANAGEUSER');
            $user_id = $p['userId'];
        }

        return User::details($user_id);
    }

    static function changeUsername()
    {
        Plugin::guard();
        $user_id = Plugin::user()['id'];
        $current_user = Plugin::user()['user'];
        
        $body = Request::bodySchema([
            'user' => [
                'alias' => 'Username',
                'type' => 'string',
                'min' => 5,
                'max' => 32,
                'regex' => '/^[a-zA-Z0-9_]+$/',
                'required' => true,
            ],
        ]);

        if ($body['user'] === $current_user) {
            throw new Exception('New username can\'t be the same as the old one', 400);
        }

        if (User::where('user', $body['user'])->where('id', '!=', $user_id)->count() > 0) {
            throw new Exception('Username already in use', 400);
        }

        if (User::where('last_user', $body['user'])->where('id', $user_id)->count() > 0) {
            throw new Exception('Cannot reuse old username', 400);
        }

        $update = User::dataUpdate($user_id, [
            'user' => $body['user'],
            'updated_at' => Date::parse('now', 'datetime'),
        ]);

        $res = static::createSignIn($user_id);


    }
}