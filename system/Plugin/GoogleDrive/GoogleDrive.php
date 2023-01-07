<?php

namespace SkiddPH\Plugin\GoogleDrive;

use SkiddPH\Core\Plugin\Key as PluginKey;
use SkiddPH\Core\Plugin\Config as PluginConfig;
use SkiddPH\Core\Plugin\DB as PluginDB;
use SkiddPH\Plugin\Database\ORM;
use SkiddPH\Plugin\Database\Database;

class GoogleDrive implements PluginKey, PluginDB
{
    const TB = 'plugin_google_drive';
    public static function key(string $key = null): string
    {
        return "GOOGLE_DRIVE";
    }

    public static function config(): PluginConfig
    {
        return new PluginConfig(self::key());
    }

    public static function db(): ORM
    {
        return Database::connect(self::config()->get('DB_ENV'));
    }

    public static function tb(): ORM
    {
        return self::db()->clear()->table(self::TB);
    }
}
