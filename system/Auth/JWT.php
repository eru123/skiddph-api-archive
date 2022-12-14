<?php

namespace Api\Auth;

use DateTime;
use Exception;
use stdClass;

use Auth;
use Api\Lib\Date;

class JWT
{

    public static $leeway = 0;

    public static $timestamp = null;

    public static $cfg_key_secret = 'JWT_SECRET';
    public static $cfg_key_algo = 'JWT_ALG';
    public static $default_algo = 'HS256';

    public static $algs = [
        'HS256' => 'SHA256',
        'HS384' => 'SHA384',
        'HS512' => 'SHA512'
    ];

    public static function decode(string $jwt): array
    {
        $cfg = Auth::config();
        $key = $cfg->get(self::$cfg_key_secret);
        $alg = $cfg->get(self::$cfg_key_algo, self::$default_algo);

        $timestamp = is_null(self::$timestamp) ? Date::now() : self::$timestamp;

        if (empty($key)) {
            throw new Exception('Invalid secret key', 401);
        }

        $tks = explode('.', $jwt);
        if (count($tks) !== 3) {
            throw new Exception('Wrong number of segments', 401);
        }

        list($headb64, $bodyb64, $cryptob64) = $tks;

        $headerRaw = self::urlsafeB64Decode($headb64);
        if (null === ($header = self::jsonDecode($headerRaw))) {
            throw new Exception('Invalid header encoding', 401);
        }

        $payloadRaw = self::urlsafeB64Decode($bodyb64);
        if (null === ($payload = self::jsonDecode($payloadRaw))) {
            throw new Exception('Invalid claims encoding', 401);
        }

        $sig = self::urlsafeB64Decode($cryptob64);


        if (is_array($payload)) {
            $payload = (object) $payload;
        }

        if (!$payload instanceof stdClass) {
            throw new Exception('Invalid claims encoding', 401);
        }

        if (empty($header->alg)) {
            throw new Exception('Empty algorithm', 401);
        }

        if (empty(self::$algs[$header->alg])) {
            throw new Exception('Algorithm not supported', 401);
        }

        if (!self::constantTimeEquals($alg, $header->alg)) {
            throw new Exception('Algorithm not allowed', 401);
        }

        if (!self::verify("{$headb64}.{$bodyb64}", $sig)) {
            throw new Exception('Signature verification failed', 401);
        }

        if (isset($payload->nbf) && $payload->nbf > ($timestamp + self::$leeway)) {
            throw new Exception(
                'Cannot handle token prior to ' . date(DateTime::ISO8601, $payload->nbf),
                401
            );
        }

        if (isset($payload->iat) && $payload->iat > ($timestamp + self::$leeway)) {
            throw new Exception(
                'Cannot handle token prior to ' . date(DateTime::ISO8601, $payload->iat),
                401
            );
        }

        if (isset($payload->exp) && ($timestamp - self::$leeway) >= $payload->exp) {
            throw new Exception('Expired token', 401);
        }

        return (array) $payload;
    }

    public static function encode(array $payload): string
    {
        $cfg = Auth::config();
        $key = $cfg->get(self::$cfg_key_secret);
        $alg = $cfg->get(self::$cfg_key_algo, self::$default_algo);
        
        $header = ['typ' => 'JWT', 'alg' => $alg];
        $segments = [];
        $segments[] = self::urlsafeB64Encode((string) self::jsonEncode($header));
        $segments[] = self::urlsafeB64Encode((string) self::jsonEncode($payload));
        $signing_input = implode('.', $segments);

        $signature = self::sign($signing_input, $key, $alg);
        $segments[] = self::urlsafeB64Encode($signature);

        return implode('.', $segments);
    }

    public static function sign(string $msg, string $key, string $alg): string
    {
        if (empty(self::$algs[$alg])) {
            throw new Exception('Algorithm not supported', 400);
        }
        $algorithm = self::$algs[$alg];
        return hash_hmac($algorithm, $msg, $key, true);
    }

    private static function verify(string $msg, string $signature): bool
    {
        $cfg = Auth::config();
        $key = $cfg->get(self::$cfg_key_secret);
        $alg = $cfg->get(self::$cfg_key_algo, self::$default_algo);

        if (empty(self::$algs[$alg])) {
            throw new Exception('Algorithm not supported');
        }

        $algorithm = self::$algs[$alg];
        $hash = hash_hmac($algorithm, $msg, $key, true);
        return self::constantTimeEquals($hash, $signature);
    }

    public static function jsonDecode(string $input)
    {
        $obj = json_decode($input, false, 512, JSON_BIGINT_AS_STRING);

        if ($errno = json_last_error()) {
            self::handleJsonError($errno);
        } elseif ($obj === null && $input !== 'null') {
            throw new Exception('Null result with non-null input');
        }
        return $obj;
    }

    public static function jsonEncode(array $input): string
    {
        if (PHP_VERSION_ID >= 50400) {
            $json = json_encode($input, JSON_UNESCAPED_SLASHES);
        } else {
            $json = json_encode($input);
        }
        if ($errno = json_last_error()) {
            self::handleJsonError($errno);
        } elseif ($json === 'null' && $input !== null) {
            throw new Exception('Null result with non-null input');
        }
        if ($json === false) {
            throw new Exception('Provided object could not be encoded to valid JSON');
        }
        return $json;
    }

    public static function urlsafeB64Decode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    public static function urlsafeB64Encode(string $input): string
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    public static function constantTimeEquals(string $left, string $right): bool
    {
        if (function_exists('hash_equals')) {
            return hash_equals($left, $right);
        }
        $len = min(self::safeStrlen($left), self::safeStrlen($right));

        $status = 0;
        for ($i = 0; $i < $len; $i++) {
            $status |= (ord($left[$i]) ^ ord($right[$i]));
        }
        $status |= (self::safeStrlen($left) ^ self::safeStrlen($right));

        return ($status === 0);
    }

    private static function handleJsonError(int $errno): void
    {
        $messages = [
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters'
        ];
        throw new Exception(
            isset($messages[$errno])
                ? $messages[$errno]
                : 'Unknown JSON error: ' . $errno,
        );
    }

    private static function safeStrlen(string $str): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($str, '8bit');
        }
        return strlen($str);
    }
}
