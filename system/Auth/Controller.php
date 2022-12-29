<?php

namespace Api\Auth;

use Request;
use Auth;
use Exception;

class Controller
{
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

        $body['pending_email'] = $body['email'];
        unset($body['email']);

        $username = $body['user'];
        $password = $body['pass'];
        unset($body['user']);
        unset($body['pass']);

        if (Email::exists($body['pending_email']) !== FALSE) {
            throw new Exception('Email already exists.', 400);
        }

        $user_id = Auth::register($username, $password, [], $body);

        $email = new Email();
        $verify_id = $email->code([
            'user_id' => $user_id,
            'email' => $body['pending_email'],
            'type' => Email::NEW_EMAIL,
            'user' => $username,
            'name' => $body['fname'] . ' ' . $body['lname'],
        ]);

        if ($verify_id) {
            $login = Auth::login($username, $password);
            if ($login) {
                return array_merge($login, [
                    'success' => "Successfully created user. Please check your email for verification code.",
                    'verify_id' => $verify_id,
                ]);
            }
        }

        throw new Exception('Successfully created user, but failed to send email verification code. Please login to re-send verification email.', 500);
    }

    static function signin()
    {
        $body = Request::bodySchema([
            'user' => [
                'alias' => 'Username',
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
        ]);

        return Auth::login($body['user'], $body['pass'], [], [], ['email']);
    }

    static function verifyEmail($params)
    {
        Auth::guard();
        $user = Auth::user();
        $verify_id = $params['verifyId'];

        $body = Request::bodySchema([
            'code' => [
                'alias' => 'Verification Code',
                'type' => 'int',
                'required' => true,
            ],
        ]);

        $code = $body['code'];
        $email = new Email();
        $email->verify($verify_id, $user['id'], $code, Email::NEW_EMAIL);
        $login = Auth::directLoginWithID($user['id'], [], [], ['email']);

        return array_merge($login, [
            'success' => "Successfully verified email.",
        ]);
    }

    static function resendEmail()
    {
        Auth::guard();
        $user_id = Auth::user()['id'];
        $user = Users::find($user_id);

        $body = Request::bodySchema([
            'email' => [
                'alias' => 'Email',
                'type' => 'email',
                'required' => true,
            ],
        ]);

        $email = new Email();
        $verify_id = $email->resend([
            'user_id' => $user['id'],
            'email' => $body['email'],
            'type' => Email::NEW_EMAIL,
            'user' => $user['user'],
            'name' => $user['fname'] . ' ' . $user['lname'],
        ]);

        if ($verify_id) {
            return [
                'success' => "Successfully sent verification email.",
                'verify_id' => $verify_id,
            ];
        }

        throw new Exception('Failed to send verification email.', 500);
    }

    static function addEmail()
    {
        Auth::guard();
        $user_id = Auth::user()['id'];
        $user = Users::find($user_id);
        $pending_email = is_array(@$user['pending_email']) ? $user['pending_email'] : (@$user['pending_email'] ? [$user['pending_email']] : []);

        $body = Request::bodySchema([
            'email' => [
                'alias' => 'Email',
                'type' => 'email',
                'required' => true,
            ],
        ]);

        $email_exists = Email::exists($body['email']);
        if (in_array($body['email'], $pending_email)) {
            throw new Exception('Email already is use.', 400);
        } else if ($email_exists !== FALSE) {
            if ($email_exists == $user_id) {
                throw new Exception('Email already is use.', 400);
            }

            throw new Exception('Email already exists.', 400);
        }

        $email = new Email();
        $verify_id = $email->addEmail($user, $body['email']);

        return [
            'success' => "Successfully sent verification email.",
            'verify_id' => $verify_id,
        ];
    }

    static function removeEmail()
    {
        Auth::guard();
        $user_id = Auth::user()['id'];

        $body = Request::bodySchema([
            'email' => [
                'alias' => 'Email',
                'type' => 'email',
                'required' => true,
            ],
        ]);

        $email = new Email();
        $email->removeEmail($user_id, $body['email']);

        return [
            'success' => "Successfully removed email.",
        ];
    }

    static function changeUsername()
    {
        Auth::guard();
        $user_id = Auth::user()['id'];

        $body = Request::bodySchema([
            'user' => [
                'alias' => 'Username',
                'type' => 'string',
                'min' => 5,
                'max' => 24,
                'required' => true,
            ],
        ]);

        $username = $body['user'];
        Users::changeUsername($user_id, $username);

        return [
            'success' => "Successfully changed username.",
        ];
    }

    static function changePassword()
    {
        Auth::guard();
        $user_id = Auth::user()['id'];

        $body = Request::bodySchema([
            'pass' => [
                'alias' => 'Password',
                'type' => 'string',
                'min' => 8,
                'required' => true,
            ],
        ]);

        $password = $body['pass'];
        Users::changePassword($user_id, $password);

        return [
            'success' => "Successfully changed password.",
        ];
    }

    static function addRole($param)
    {
        Auth::accessControl('SUPERADMIN,ASSIGNURL');
        $user_id = $param['userId'];

        $assigner_id = Auth::user()['id'];
        $assigner_rl = Auth::user()['roles'];

        if (in_array('ASSIGNURL', $assigner_rl) && $assigner_id == $user_id) {
            throw new Exception('Cannot assign role to self.', 400);
        }

        $user = Users::find($user_id, true, false);
        if (empty($user)) {
            throw new Exception('User does not exist.', 400);
        }

        $body = Request::bodySchema([
            'role' => [
                'alias' => 'Role',
                'type' => 'string',
                'required' => true,
            ],
        ]);

        $role = $body['role'];
        Users::addRole($user_id, $role);

        return [
            'success' => "Successfully added role.",
        ];
    }

    static function removeRole($param)
    {
        Auth::accessControl('SUPERADMIN,ASSIGNURL');
        $user_id = $param['userId'];

        $assigner_id = Auth::user()['id'];
        $assigner_rl = Auth::user()['roles'];

        if (in_array('ASSIGNURL', $assigner_rl) && $assigner_id == $user_id) {
            throw new Exception('Cannot remove role from self.', 400);
        }

        $body = Request::bodySchema([
            'role' => [
                'alias' => 'Role',
                'type' => 'string',
                'required' => true,
            ],
        ]);

        $role = $body['role'];
        Users::removeRole($user_id, $role);

        return [
            'success' => "Successfully removed role.",
        ];
    }
}
