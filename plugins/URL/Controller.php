<?php

namespace Plugin\URL;

use Request;
use Auth;
use Api\Lib\Rand;
use Api\Auth\JWT;

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

        Auth::register($username, $password, $roles, $body);
        
        // generate email verification token
    }

    static function generate_verify_token()
    {
        $code = Rand::int(100000, 999999);
        return JWT::encode([
            'code' => $code,
            'exp' => 'now + 1d',
        ]);
    }
}
