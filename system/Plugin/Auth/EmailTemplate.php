<?php

namespace SkiddPH\Plugin\Auth;

use Exception;
use SkiddPH\Plugin\SMTP\SMTP;

class EmailTemplate
{
    static function generate(SMTP &$smtp, array $data) {
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
        $smtp->text($msg);
    }

}