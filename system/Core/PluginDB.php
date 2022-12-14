<?php

use Api\Database\ORM;

interface PluginDB
{
    /**
     * Returns the database ORM instance
     * @return ORM
     */
    static function db(): ORM;
}
