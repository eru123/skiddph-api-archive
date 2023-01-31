<?php

namespace SkiddPH\Plugin\Auth;

use SkiddPH\Helper\Date;
use SkiddPH\Helper\Rand;
use SkiddPH\Model\EmailVerification;
use SkiddPH\Model\UserEmail;
use SkiddPH\Plugin\DB\Row;
use SkiddPH\Plugin\DB\DB;
use SkiddPH\Plugin\SMTP\SMTP;
use Exception;

class Email
{
    public static function send($data = []): int
    {
        $required = ['user_id', 'email', 'type', 'name', 'user'];
        foreach ($required as $key) {
            if (empty($data[$key])) {
                throw new Exception('Invalid ' . $key, 400);
            }
        }

        if (method_exists(static::class, 'send__' . $data['type'])) {
            static::{'send__' . $data['type']}($data['email']);
        }

        $expm = Date::parse(pcfg('email_verification_expire_at', '24mins'), 'minutes');
        $exp = Date::parse("now - $expm minutes", 'datetime');
        $jwt_exp = Date::parse("now + $expm minutes");

        $ver = Date::parse(pcfg('auth.email_resend_if_time', '5mins'), 'minutes');
        $ver = Date::parse("now - $ver minutes", 'datetime');

        $verify = EmailVerification::where('user_id', $data['user_id'])
            ->where('created_at', 'gte', DB::raw("TIMESTAMP(?)", [$ver]))
            ->where('created_at', 'gte', DB::raw("TIMESTAMP(?)", [$exp]))
            ->where('type', $data['type'])
            ->where('status', false)
            ->order('created_at', 'desc')
            ->first();

        if ($verify) {
            return $verify->id;
        }

        try {
            $data['code'] = Rand::int(100000, 999999);
            $verify_id = EmailVerification::insert([
                'user_id' => $data['user_id'],
                'code' => $data['code'],
                'email' => $data['email'],
                'type' => $data['type'],
                'status' => false,
                'created_at' => Date::parse('now', 'datetime')
            ]);

            if (!$verify_id) {
                throw new Exception('Failed to insert verification code', 500);
            }

            $data['token'] = JWT::encode([
                'verify_id' => $verify_id,
                'user_id' => $data['user_id'],
                'code' => $data['code'],
                'type' => $data['type'],
                'exp' => $jwt_exp,
                'iat' => 'now'
            ]);

            $email = SMTP::use ();
            EmailTemplate::generate($email, $data);
            $email->override(['from_name' => pcfg('app.name', 'App') . ' Security'])->send();

            return $verify_id;
        } catch (Exception $e) {
            throw new Exception('Failed to create verification code', 500);
        }
    }

    public static function send__new($email)
    {
        if (!pcfg('auth.allow_signup', true)) {
            throw new Exception('Sign up is not allowed', 403);
        }

        if (pcfg('auth.email_auto_verify')) {
            throw new Exception('Email auto verify is enabled no need to send verification code', 400);
        }

        if (UserEmail::inUse($email)) {
            throw new Exception('Email already in use', 400);
        }
    }

    public static function verify(array $data)
    {
        $required = ['verify_id', 'user_id', 'code', 'type'];
        foreach ($required as $key) {
            if (empty($data[$key])) {
                throw new Exception('Invalid ' . $key, 400);
            }
        }

        $expm = Date::parse(pcfg('email_verification_expire_at', '24mins'), 'minutes');
        $exp = Date::parse("now - $expm minutes", 'datetime');
        $email = EmailVerification::where('id', $data['verify_id'])
            ->where('user_id', $data['user_id'])
            ->where('created_at', 'gte', DB::raw("TIMESTAMP(?)", [$exp]))
            ->where('type', $data['type'])
            ->where('code', $data['code'])
            ->where('status', false)
            ->order('created_at', 'desc')
            ->first();

        if (!$email) {
            throw new Exception('Invalid verification code', 400);
        }

        $email->status = true;
        $email->update();

        if (method_exists(static::class, 'onverify__' . $data['type'])) {
            return static::{'onverify__' . $data['type']}($email);
        }

        throw new Exception('Invalid verification type', 400);
    }
    protected static function onverify__new(Row $email)
    {
        if (UserEmail::inUse($email->email)) {
            throw new Exception('Email already verified', 400);
        }

        $email = UserEmail::where('user_id', $email->user_id)
            ->where('email', $email->email)
            ->where('verified', false)
            ->first();
        
        if (!$email) {
            throw new Exception('Invalid email', 400);
        }

        $email->verified = true;
        $email->update();

        return true;
    }
}