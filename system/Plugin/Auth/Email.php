<?php

namespace SkiddPH\Plugin\Auth;

use SkiddPH\Helper\Date;
use SkiddPH\Helper\Rand;
use SkiddPH\Plugin\SMTP\SMTP;
use SkiddPH\Plugin\Auth\Model\Email as EmailModel;
use SkiddPH\Plugin\Auth\Model\Info as InfoModel;
use SkiddPH\Plugin\Database\Helper;
use Exception;

class Email
{
    const NEW_EMAIL = 'NEW_EMAIL';
    const RESET_PASSWORD = 'RESET_PASSWORD';
    public static function use (): self
    {
        return new self();
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
        $exp = pcfg('email_verification_expire_at', 'now + 24mins');
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
            $smtp = SMTP::use (pcfg('smtp.smtp', 'default'));
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

    public static function exists($email)
    {
        $orm = Auth::db();
        $user = $orm->table(InfoModel::TB)
            ->where(['name' => $orm->quote('email')])
            ->and()
            ->where(['value' => $orm->quote(Helper::jsonEncode($email))])
            ->readOne()
            ->arr();

        if (empty($user)) {
            return FALSE;
        }

        return $user['user_id'];
    }

    public function addEmail($user, $email)
    {
        if (empty($user)) {
            throw new Exception('Invalid user', 400);
        }

        $orm = Auth::db();

        if (is_array($user)) {
            $user_id = $user['id'];
        } else {
            $user_id = $user;
            $user = null;
        }

        try {
            $orm->begin();
            $affected = $orm->table(InfoModel::TB)
                ->data([
                    [
                        'user_id' => $user_id,
                        'name' => 'pending_email',
                        'value' => Helper::jsonEncode($email)
                    ]
                ])
                ->insert()
                ->rowCount();

            if (!$affected) {
                throw new Exception('Failed to add email', 500);
            }

            if (empty($user)) {
                $user = Users::find($user_id);

                if (empty($user)) {
                    throw new Exception('Invalid user', 400);
                }
            }

            $orm->commit();
        } catch (Exception $e) {
            $orm->rollback();
            throw new Exception('Failed to add email', 500);
        }

        $verify_id = $this->code([
            'user_id' => $user_id,
            'email' => $email,
            'type' => self::NEW_EMAIL,
            'user' => $user['user'],
            'name' => trim(@$user['fname'] . ' ' . @$user['lname']) ?? "User",
        ]);

        if (empty($verify_id)) {
            throw new Exception('Email added but failed to send verification code', 500);
        }

        return $verify_id;
    }

    public function removeEmail($user, $email)
    {
        if (empty($user)) {
            throw new Exception('Invalid user', 400);
        }

        $orm = Auth::db();

        if (is_array($user)) {
            $user_id = $user['id'];
        } else {
            $user_id = $user;
            $user = null;
        }

        try {
            $orm->begin();
            $affected = $orm->table(InfoModel::TB)
                ->where(['user_id' => $user_id])
                ->and()
                ->where(['value' => $orm->quote(Helper::jsonEncode($email))])
                ->and()
                ->where(['name' => ['IN' => [$orm->quote('email'), $orm->quote('pending_email')]]])
                ->delete()
                ->rowCount();

            if (!$affected) {
                throw new Exception('Failed to remove email', 500);
            }

            $orm->commit();
        } catch (Exception $e) {
            $orm->rollback();
            throw new Exception('Failed to remove email' . $e->getMessage() . '>>:' . $orm->getLastQuery(), 500);
        }

        return true;
    }
}