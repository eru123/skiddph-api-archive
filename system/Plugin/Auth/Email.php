<?php

namespace SkiddPH\Plugin\Auth;

use SkiddPH\Helper\Date;
use SkiddPH\Helper\Rand;
use SkiddPH\Model\EmailVerification;
use SkiddPH\Model\UserInfo;
use SkiddPH\Plugin\DB\Row;
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

        $exp = pcfg('email_verification_expire_at', 'now + 24mins');
        $verify = EmailVerification::where('user_id', $data['user_id'])
            ->where('updated_at', 'lte', Date::parse(pcfg('auth.email_resend_if_time', 'now - 5mins'), 'datetime'))
            ->where('updated_at', 'gte', Date::parse("now - ($exp)", 'datetime'))
            ->where('type', $data['type'])
            ->first();

        if ($verify) {
            return $verify->id;
        }



        try {
            $data['code'] = Rand::int(100000, 999999);
            $verify_id = EmailVerification::newCode($data);
            if (!$verify_id) {
                throw new Exception('Failed to create verification code', 500);
            }

            $data['token'] = JWT::encode([
                'verify_id' => $verify_id,
                'user_id' => $data['user_id'],
                'code' => $data['code'],
                'type' => $data['type'],
                'exp' => $exp,
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

    public static function verify(array $data)
    {
        $required = ['user_id', 'code', 'type'];
        foreach ($required as $key) {
            if (empty((string) $data[$key])) {
                throw new Exception('Invalid ' . $key, 400);
            }
        }

        $exp = pcfg('email_verification_expire_at', 'now + 24mins');
        $email = EmailVerification::where('user_id', $data['user_id'])
            ->where('updated_at', 'gte', Date::parse("now - ($exp)", 'datetime'))
            ->where('type', $data['type'])
            ->where('code', $data['code'])
            ->where('status', 0)
            ->order('updated_at', 'desc')
            ->first();

        if (!$email) {
            throw new Exception('Invalid verification code', 400);
        }

        if (method_exists(static::class, 'onverify__' . $data['type'])) {
            return static::{'onverify__' . $data['type']}($email);
        }

        throw new Exception('Invalid verification type', 400);
    }

    protected static function onverify__new(Row $email)
    {
        $email->status = 1;
        $email->update();

        if (UserInfo::isValueExists('email', $email->email)) {
            throw new Exception('Email already verified', 400);
        }

        UserInfo::removeFor($email->user_id, [
            'pending_email' => $email->email,
        ]);

        if (UserInfo::insertFor($email->user_id, ['email' => $email->email])) {
            return \SkiddPH\Controller\Auth::createSignIn($email->user_id);
        }

        throw new Exception('Failed to verify email', 500);
    }
}