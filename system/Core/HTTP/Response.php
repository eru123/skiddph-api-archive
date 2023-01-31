<?php

namespace SkiddPH\Core\HTTP;

class Response
{
    static function redirect(string $path)
    {
        header("Location: $path");
        exit;
    }
}