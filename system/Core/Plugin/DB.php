<?php

namespace SkiddPH\Core\Plugin;

use SkiddPH\Plugin\Database\ORM;

interface DB
{
    /**
     * Returns the database ORM instance
     * @return ORM
     */
    static function db(): ORM;
}
