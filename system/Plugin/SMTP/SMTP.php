<?php

namespace SkiddPH\Plugin\SMTP;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;

class SMTP
{
    private $smtp_opts = [];
    private $to = [];
    private $cc = [];
    private $bcc = [];

    public static function use (string $key = null): self
    {
        if (empty($key)) {
            $key = pcfg('smtp.smtp', 'default');
        }
        return new self($key);
    }

    public function __construct(string $key = null)
    {
        $this->smtp_opts = pcfg('smtp.smtps.' . ($key ?: pcfg('smtp.smtp', 'default')));

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
    }

    private static function validateEmail($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function __set($name, $value)
    {
        if (in_array($name, ['to', 'cc', 'bcc'])) {
            $this->{$name} = array_merge($this->{$name}, $this->extractEmail($value));
            $this->{$name} = array_unique($this->{$name});
            $this->{$name} = array_map('trim', $this->{$name});
            $this->{$name} = array_filter($this->{$name}, 'static::validateEmail');
            return;
        }
        $this->smtp_opts[$name] = $value;
    }

    public function __get($name)
    {
        if (in_array($name, ['to', 'cc', 'bcc'])) {
            return $this->{$name};
        }
        return $this->smtp_opts[$name] ?? null;
    }

    public function __call($name, $arguments)
    {
        if (in_array($name, ['to', 'cc', 'bcc'])) {
            $this->{$name} = array_merge($this->{$name}, $this->extractEmail($arguments[0]));
            $this->{$name} = array_unique($this->{$name});
            $this->{$name} = array_map('trim', $this->{$name});
            $this->{$name} = array_filter($this->{$name}, 'static::validateEmail');
            return $this;
        }
        $this->smtp_opts[$name] = @$arguments[0];
        return $this;
    }

    public static function __callStatic($name, $arguments)
    {
        $smtp = static::use ();
        return $smtp->__call($name, $arguments);
    }

    public function __toString()
    {
        return json_encode($this->smtp_opts);
    }

    private static function extractEmail($emails): array
    {
        if (is_string($emails)) {
            $emails = preg_split('/[,;]+/', $emails);
        }

        return $emails;
    }

    public function send(): bool
    {
        $opt = $this->smtp_opts;
        $mail = new PHPMailer(true);
        $mail->isSMTP();

        $mail->SMTPDebug = isset($opt['debug']) ? $opt['debug'] : 0;

        $mail->Host     = $opt['host'];
        $mail->Port     = $opt['port'];
        $mail->Username = $opt['user'];
        $mail->Password = $opt['pass'];

        if (isset($opt['secure']))      $mail->SMTPSecure       = $opt['secure'];
        if (isset($opt['auth']))        $mail->SMTPAuth         = $opt['auth'];
        if (isset($opt['options']))     $mail->SMTPOptions      = $opt['options'];
        if (isset($opt['charset']))     $mail->CharSet          = $opt['charset'];
        if (isset($opt['encoding']))    $mail->Encoding         = $opt['encoding'];
        if (isset($opt['priority']))    $mail->Priority         = $opt['priority'];
        if (isset($opt['wordwrap']))    $mail->WordWrap         = $opt['wordwrap'];
        if (isset($opt['timeout']))     $mail->Timeout          = $opt['timeout'];
        if (isset($opt['keepalive']))   $mail->SMTPKeepAlive    = $opt['keepalive'];
        if (isset($opt['text']))        $mail->AltBody          = $opt['text'];
        if (isset($opt['subject']))     $mail->Subject          = $opt['subject'];

        if (isset($opt['html'])) {
            $mail->Body = $opt['html'];
            $mail->isHTML(true);
        } else if (isset($opt['text'])) {
            $mail->Body = nl2br($opt['text']);
            $mail->isHTML(true);
        }

        if (isset($opt['from']))        $mail->setFrom($opt['from'], $opt['from_name']);
        if (isset($opt['reply_to']))    $mail->addReplyTo($opt['reply_to'], $opt['reply_to_name']);

        foreach ($this->to as $email)   $mail->addAddress($email);
        foreach ($this->cc as $email)   $mail->addCC($email);
        foreach ($this->bcc as $email)  $mail->addBCC($email);

        return $mail->send();
    }

    public function override(array $opts): self
    {
        $this->smtp_opts = array_merge($this->smtp_opts, $opts);
        return $this;
    }
}