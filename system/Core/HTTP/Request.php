<?php

namespace SkiddPH\Core\HTTP;

use Exception;

class Request
{
    static function ip(): string
    {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            return $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            return $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        return ':::';
    }

    static function body(): array
    {
        $body = [];
        if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
            $body = json_decode(file_get_contents('php://input'), true);
        } else {
            $body = $_POST;
        }
        return $body;
    }

    static function query($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    static function header($key = null, $default = null)
    {
        if ($key === null) {
            return getallheaders();
        }
        return getallheaders()[$key] ?? $default;
    }

    static function allowCORS(string $origin = null)
    {
        if ($origin === null) {
            if (isset($_SERVER['HTTP_ORIGIN'])) {
                $origin = $_SERVER['HTTP_ORIGIN'];
            } else {
                $origin = '*';
            }
        }

        $custom_headers = ['X-Authorization'];

        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Credentials: true');

        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
            }
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}, " . implode(', ', $custom_headers));
            }
            exit;
        }
    }

    static function bodySchema(array $schema)
    {
        $body = self::body();

        $default = [
            'type' => 'string',
            'required' => false,
            'default' => null,
        ];

        foreach ($schema as $key => $opts) {
            $alias = @$opts['alias'] ?? $key;

            if (is_string($opts) || empty($opts)) {
                $opts = ['type' => $opts];
            }

            $opts = array_merge($default, $opts);

            if ($opts['required'] && !isset($body[$key])) {
                throw new Exception("Missing required field: $alias", 400);
            } else if (!$opts['required'] && !isset($body[$key])) {
                $body[$key] = $opts['default'];
            }

            $opts['type'] = strtolower($opts['type']);
            if ($opts['type'] === 'string' || $opts['type'] === 'email') {
                $body[$key] = (string) $body[$key];
            } else if ($opts['type'] === 'int') {
                $body[$key] = (int) $body[$key];
            } else if ($opts['type'] === 'float') {
                $body[$key] = (float) $body[$key];
            } else if ($opts['type'] === 'bool') {
                $body[$key] = (bool) $body[$key];
            } else if ($opts['type'] === 'array') {
                $body[$key] = (array) $body[$key];
            } else if ($opts['type'] === 'object') {
                $body[$key] = (object) $body[$key];
            } else if ($opts['type'] === 'json') {
                $body[$key] = json_decode($body[$key], true);
            }

            if ($opts['type'] === 'string' && isset($opts['min']) && strlen($body[$key]) < $opts['min']) {
                throw new Exception("Field $alias must be at least {$opts['min']} characters long", 400);
            }

            if ($opts['type'] === 'string' && isset($opts['max']) && strlen($body[$key]) > $opts['max']) {
                throw new Exception("Field $alias must be at most {$opts['max']} characters long", 400);
            }

            if ($opts['type'] === 'string' && isset($opts['regex']) && !preg_match($opts['regex'], $body[$key])) {
                throw new Exception("Field $alias must match the regex {$opts['regex']}", 400);
            }

            if ($opts['type'] === 'email' && !filter_var($body[$key], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Field $alias must be a valid email address", 400);
            }

            if (($opts['type'] === 'int' || $opts['type'] === 'float') && isset($opts['min']) && $body[$key] < $opts['min']) {
                throw new Exception("Field $alias must be at least {$opts['min']}", 400);
            }

            if (($opts['type'] === 'int' || $opts['type'] === 'float') && isset($opts['max']) && $body[$key] > $opts['max']) {
                throw new Exception("Field $alias must be at most {$opts['max']}", 400);
            }

            if ($opts['type'] === 'array' && isset($opts['min']) && count($body[$key]) < $opts['min']) {
                throw new Exception("Field $alias must have at least {$opts['min']} items", 400);
            }

            if ($opts['type'] === 'array' && isset($opts['max']) && count($body[$key]) > $opts['max']) {
                throw new Exception("Field $alias must have at most {$opts['max']} items", 400);
            }
        }

        return $body;
    }
}
