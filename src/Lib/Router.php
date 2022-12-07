<?php

namespace App\Lib;

class Router
{
    static function isMatch(string $url, string $pattern): bool
    {
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = str_replace('*', '.*', $pattern);
        $pattern = str_replace('?', '.?', $pattern);
        $pattern = "/^$pattern$/";
        return preg_match($pattern, $url);
    }
}
