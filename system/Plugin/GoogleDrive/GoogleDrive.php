<?php

namespace SkiddPH\Plugin\GoogleDrive;

use SkiddPH\Plugin\Database\ORM;
use SkiddPH\Plugin\Database\Database;

class GoogleDrive
{
    const TB = 'plugin_google_drive';
    
    public static function db(): ORM
    {
        return Database::connect();
    }

    public static function tb(): ORM
    {
        return self::db()->clear()->table(self::TB);
    }
}