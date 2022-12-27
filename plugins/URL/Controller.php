<?php

namespace Plugin\URL;

use Request;
use Auth;
use Api\Auth\Email;
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

        $roles = [
            'URLGENERATOR'
        ];

        $user_id = Auth::register($username, $password, $roles, $body);

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
                'regex' => '/^[a-zA-Z0-9_]+$/',
                'required' => true,
            ],
            'pass' => [
                'alias' => 'Password',
                'type' => 'string',
                'min' => 8,
                'required' => true,
            ],
        ]);

        return Auth::login($body['user'], $body['pass']);
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

        return [
            'success' => "Successfully verified email.",
        ];
    }
}