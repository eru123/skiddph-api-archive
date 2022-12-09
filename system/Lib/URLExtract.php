<?php

namespace Api\Lib;

class URLExtract
{
    static function query(string $url): array|null
    {
        $query = parse_url($url, PHP_URL_QUERY);
        $query = explode('&', $query);
        $query = array_map(function ($item) {
            $item = explode('=', $item);
            return [$item[0] => $item[1]];
        }, $query);
        $query = array_reduce($query, function ($carry, $item) {
            $carry[key($item)] = $item[key($item)];
            return $carry;
        }, []);
        return $query;
    }

    static function path(string $url): string|null
    {
        $path = parse_url($url, PHP_URL_PATH);
        return $path;
    }

    static function host(string $url): string|null
    {
        $host = parse_url($url, PHP_URL_HOST);
        return $host;
    }

    static function port(string $url): int|null
    {
        $port = parse_url($url, PHP_URL_PORT);
        return $port;
    }

    static function scheme(string $url): string|null
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        return $scheme;
    }

    static function fragment(string $url): string|null
    {
        $fragment = parse_url($url, PHP_URL_FRAGMENT);
        return $fragment;
    }

    static function user(string $url): string|null
    {
        $user = parse_url($url, PHP_URL_USER);
        return $user;
    }

    static function pass(string $url): string|null
    {
        $pass = parse_url($url, PHP_URL_PASS);
        return $pass;
    }

    static function from(string $url): array
    {
        return [
            'query' => self::query($url),
            'path' => self::path($url),
            'host' => self::host($url),
            'port' => self::port($url),
            'scheme' => self::scheme($url),
            'fragment' => self::fragment($url),
            'user' => self::user($url),
            'pass' => self::pass($url),
        ];
    }
}
