<?php

namespace eru123\email;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;

class SMTP
{
    public static function bootstrap($state)
    {
    }

    public static function send($data)
    {
        $bcc = @$data['bcc'] ?? [];
        $cc = @$data['cc'] ?? [];
        $to = @$data['to'] ?? [];
        $from = @$data['from'] ?? '';
        $subject = @$data['subject'] ?? '';
        $body = @$data['body'] ?? '';
        $attachments = @$data['attachments'] ?? [];

        $smtp = new PHPMailer(true);
        $smtp->isSMTP();

        $cfg = pcfg('smtp.smtps.' . pcfg('smtp.smtp', 'default'));

        if (empty($cfg)) {
            throw new Exception('Invalid SMTP configuration', 400);
        }

        if (empty($cfg['host'])) {
            throw new Exception('Invalid SMTP host', 400);
        }

        if (empty($cfg['port'])) {
            throw new Exception('Invalid SMTP port', 400);
        }

        if (empty($cfg['user'])) {
            throw new Exception('Invalid SMTP username', 400);
        }

        if (empty($cfg['pass'])) {
            throw new Exception('Invalid SMTP password', 400);
        }

        if (empty($cfg['from'])) {
            throw new Exception('Invalid SMTP from', 400);
        }

        if (empty($cfg['from_name'])) {
            throw new Exception('Invalid SMTP from name', 400);
        }

    }
}
