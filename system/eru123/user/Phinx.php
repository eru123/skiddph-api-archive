<?php

namespace eru123\user;

class Phinx
{
    public static function migrations()
    {
        return __DIR__ . '/db/migrations';
    }
}