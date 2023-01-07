<?php

namespace SkiddPH\Helper;

class Rand
{
    static function int(int $min = 0, int $max = 0)
    {
        if ($max === 0) {
            $max = getrandmax();
        }
        return rand($min, $max);
    }
}