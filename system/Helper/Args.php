<?php

namespace SkiddPH\Helper;

class Args
{
    static function create(...$args)
    {
        return $args ?? [];
    }
}
