<?php

class Args
{
    static function create(...$args)
    {
        return $args ?? [];
    }
}
