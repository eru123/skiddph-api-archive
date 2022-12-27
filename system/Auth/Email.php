<?php

namespace Api\Auth;

use Api\Lib\Date;
use Api\Lib\Rand;
use Auth;
use SMTP;
use Api\Auth\Model\Email as EmailModel;
use Api\Auth\Model\Info as InfoModel;
use Api\Database\Helper;
use Exception;

class Email
{
    private $smtp_key = 'default';
    const NEW_EMAIL = 'NEW_EMAIL';
    const RESET_PASSWORD = 'RESET_PASSWORD';
    public static function use(string $key): self
    {
        return new self($key);
    }

    public function __construct(string $key = null)
    {
        if (!empty($key)) {
            $this->smtp_key = $key;
        }
    }

    public function code($opts): int
    {
        $required = ['user_id', 'email', 'type'];
        foreach ($required as $key) {
            if (empty($opts[$key])) {
                throw new Exception('Invalid ' . $key, 400);
            }
        }

        $code = Rand::int(100000, 999999);
        $cfg = Auth::config();
        $exp = $cfg->get('EMAIL_VERIFICATION_EXPIRE_AT', 'now + 24mins');
        $data = [
            'code' => $code,
            'exp' => $exp
        ];
        $payload = array_merge($opts, $data);
        $token = JWT::encode($payload);
        $orm = Auth::db();

        try {
            $orm->begin();
            $verify_id = EmailModel::new_code($opts['user_id'], $token);
            if (!$verify_id) {
                throw new Exception('Failed to create verification code', 500);
            }
            $orm->commit();
        } catch (Exception $e) {
            $orm->rollback();
            throw new Exception('Failed to create verification code', 500);
        }

        try {
            $smtp = SMTP::use($this->smtp_key);
            $smtp->to($opts['email']);
            $smtp->subject('Email Verification');
            $msg = !empty(@$opts['name']) ? 'Hi ' . $opts['name'] . ', ' : 'Hi, ';
            $msg .= !empty(@$opts['user']) ? 'with username @' . $opts['user'] . ', ' : '';
            $msg .= 'your verification code is ' . $code . '.';
            $smtp->text($msg);
            $smtp->send();
            return $verify_id;
        } catch (Exception $e) {
            throw new Exception('Failed to send verification code', 500);
        }
    }

    public function verify($verify_id, $user_id, $code, $type)
    {
        $verify = Auth::db()->table(EmailModel::TB)
            ->where(['id' => $verify_id])
            ->and()
            ->where(['user_id' => $user_id])
            ->readOne()
            ->arr();

        if (empty($verify)) {
            throw new Exception('Invalid verification step', 401);
        }


        try {
            $decoded = JWT::decode($verify['token']);
        } catch (Exception $e) {
            throw new Exception('Verification Expired', 401);
        }

        if (intval($decoded['code']) - intval($code) !== 0 || empty(intval($decoded['code'])) || $type != $decoded['type']) {
            throw new Exception('Invalid verification code', 401);
        }

        $orm = Auth::db();

        try {
            $orm->begin();
            $affected = $orm->table(InfoModel::TB)
                ->where(['user_id' => $user_id])
                ->and()
                ->where(['name' => $orm->quote('pending_email')])
                ->and()
                ->where(['value' => $orm->quote(Helper::jsonEncode($decoded['email']))])
                ->data([
                    [
                        'name' => 'email'
                    ]
                ])
                ->update()
                ->rowCount();

            if (!$affected) {
                throw new Exception('Invalid verification email', 400);
            }

            $orm->commit();
        } catch (Exception $e) {
            $orm->rollback();
            throw new Exception('Failed to verify email', 500);
        }

        return true;
    }

    public function verifyNewEmail($verify_id, $user_id, $code)
    {
        return $this->verify($verify_id, $user_id, $code, self::NEW_EMAIL);
    }

    public function verifyResetPassword($verify_id, $user_id, $code)
    {
        return $this->verify($verify_id, $user_id, $code, self::RESET_PASSWORD);
    }

    public function resend($opts)
    {
        $required = ['user_id', 'email', 'type'];
        foreach ($required as $key) {
            if (empty($opts[$key])) {
                throw new Exception('Invalid ' . $key, 400);
            }
        }

        $emailObj = new EmailModel();
        $verify = $emailObj->get([
            'user_id' => $opts['user_id'],
        ])->arr();

        if (empty($verify)) {
            return $this->code($opts);
        }

        if (Date::parse($verify['updated_at']) <= Date::parse('now - 5mins')) {
            return $this->code($opts);
        }

        return $verify['id'];
    }
}
