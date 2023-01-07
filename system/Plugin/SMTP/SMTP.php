<?php

namespace SkiddPH\Plugin\SMTP;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use SkiddPH\Core\Plugin\Config as PluginConfig;

class SMTP
{
    private static $key = "SMTPS";
    private $smtp_opts = null;
    private $to = [];
    private $cc = [];
    private $bcc = [];

    static function config(): PluginConfig
    {
        return new PluginConfig(self::$key);
    }

    public static function use (string $key = 'default'): self
    {
        return new self($key);
    }

    public function __construct(string $key)
    {
        $cfg = SMTP::config();
        $this->smtp_opts = $cfg->get($key);

        if (empty($this->smtp_opts)) {
            throw new Exception('Invalid SMTP configuration', 400);
        }

        if (empty($this->smtp_opts['host'])) {
            throw new Exception('Invalid SMTP host', 400);
        }

        if (empty($this->smtp_opts['port'])) {
            throw new Exception('Invalid SMTP port', 400);
        }

        if (empty($this->smtp_opts['user'])) {
            throw new Exception('Invalid SMTP username', 400);
        }

        if (empty($this->smtp_opts['pass'])) {
            throw new Exception('Invalid SMTP password', 400);
        }

        if (empty($this->smtp_opts['from'])) {
            throw new Exception('Invalid SMTP from', 400);
        }

        if (empty($this->smtp_opts['from_name'])) {
            throw new Exception('Invalid SMTP from name', 400);
        }

        if (empty($this->smtp_opts['secure'])) {
            $this->smtp_opts['secure'] = 'tls';
        }

        if (empty($this->smtp_opts['auth'])) {
            $this->smtp_opts['auth'] = true;
        }

        if (empty($this->smtp_opts['debug'])) {
            $this->smtp_opts['debug'] = 0;
        }

        if (empty($this->smtp_opts['charset'])) {
            $this->smtp_opts['charset'] = 'utf-8';
        }

        if (empty($this->smtp_opts['encoding'])) {
            $this->smtp_opts['encoding'] = '8bit';
        }

        if (empty($this->smtp_opts['priority'])) {
            $this->smtp_opts['priority'] = 3;
        }

        if (empty($this->smtp_opts['wordwrap'])) {
            $this->smtp_opts['wordwrap'] = 0;
        }

        if (empty($this->smtp_opts['timeout'])) {
            $this->smtp_opts['timeout'] = 30;
        }

        if (empty($this->smtp_opts['keepalive'])) {
            $this->smtp_opts['keepalive'] = false;
        }
    }

    public function to($email): self
    {
        if (is_array($email)) {
            $this->to = array_merge($this->to, $email);
        } else if (is_string($email)) {
            $this->to = array_merge($this->to, preg_split('/[,;]+/', $email));
        }

        return $this;
    }

    public function cc($email): self
    {
        if (is_array($email)) {
            $this->cc = array_merge($this->cc, $email);
        } else if (is_string($email)) {
            $this->cc = array_merge($this->cc, preg_split('/[,;]+/', $email));
        }

        return $this;
    }

    public function bcc($email): self
    {
        if (is_array($email)) {
            $this->bcc = array_merge($this->bcc, $email);
        } else if (is_string($email)) {
            $this->bcc = array_merge($this->bcc, preg_split('/[,;]+/', $email));
        }

        return $this;
    }

    public function text(string $text): self
    {
        $this->smtp_opts['text'] = $text;
        return $this;
    }

    public function subject(string $subject): self
    {
        $this->smtp_opts['subject'] = $subject;
        return $this;
    }

    public function html(bool $html = true): self
    {
        $this->smtp_opts['html'] = $html;
        return $this;
    }

    public function send(): bool
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->SMTPDebug = $this->smtp_opts['debug'];
        $mail->Host = $this->smtp_opts['host'];
        $mail->Port = $this->smtp_opts['port'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->SMTPAuth = true;
        $mail->Username = $this->smtp_opts['user'];
        $mail->Password = $this->smtp_opts['pass'];

        $mail->setFrom($this->smtp_opts['from'], $this->smtp_opts['from_name']);
        $mail->addReplyTo($this->smtp_opts['from'], $this->smtp_opts['from_name']);

        foreach ($this->to as $email) {
            $mail->addAddress($email);
        }

        foreach ($this->cc as $email) {
            $mail->addCC($email);
        }

        foreach ($this->bcc as $email) {
            $mail->addBCC($email);
        }

        $mail->Subject = $this->smtp_opts['subject'];

        $is_html = (bool) @$this->smtp_opts['html'];

        $mail->Body = $is_html && !empty($this->smtp_opts['html']) ? $this->smtp_opts['html'] : nl2br($this->smtp_opts['text']);
        $mail->AltBody = $this->smtp_opts['text'];
        $mail->isHTML($is_html);

        return $mail->send();
    }
}