<?php

namespace SkiddPH\Plugin\Auth;

use SkiddPH\Helper\Date;
use SkiddPH\Helper\Rand;
use SkiddPH\Model\EmailVerification;
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

        $data['code'] = Rand::int(100000, 999999);

        try {
            $email = SMTP::use ();
            EmailTemplate::generate($email, $data);
            $email->override(['from_name' => pcfg('app.name','App').' Security'])->send();
            $verify_id = EmailVerification::newCode($data);
            if (!$verify_id) {
                throw new Exception('Failed to create verification code', 500);
            }

            return $verify_id;
        } catch (Exception $e) {
            throw new Exception('Failed to create verification code', 500);
        }
    }
}