<?php

namespace eru123\email;

class Phinx
{
    public static function migrations()
    {
        return __DIR__ . '/db/migrations';
    }
}