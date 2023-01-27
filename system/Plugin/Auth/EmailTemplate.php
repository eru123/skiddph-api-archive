<?php

namespace SkiddPH\Plugin\Auth;

use Exception;
use SkiddPH\Plugin\SMTP\SMTP;

class EmailTemplate
{   
    static function generate(SMTP &$smtp, array $data)
    {
        $type = $data['type'];
        if (method_exists(static::class, $type)) {
            static::$type($smtp, $data);
        } else {
            throw new Exception('Invalid email type', 400);
        }
    }

    static function new (SMTP &$smtp, array $data): void
    {
        $required = ['user_id', 'user', 'email', 'code', 'name'];
        foreach ($required as $key) {
            if (empty($data[$key])) {
                throw new Exception('Invalid ' . $key, 400);
            }
        }
        
        $smtp->to($data['email']);
        $smtp->subject('Email Verification: ' . $data['code']);
        $msg = !empty(@$data['name']) ? 'Hi ' . $data['name'] . ', ' : 'Hi, ';
        $msg .= !empty(@$data['user']) ? 'with username @' . $data['user'] . ', ' : '';
        $msg .= 'your verification code is ' . $data['code'] . '.';

        $link = pcfg('app.api', 'https://api.local') . '/api/v1/auth/email/verify/' . $data['token'];
        $msg .= "<br>Or if you prefer, you can click this link to verify your email: <a href='$link'>VERIFY EMAIL</a>";
        $msg .= '<br><br>If you did not request this verification code, please ignore this email.';
        $smtp->text($msg);
    }

}