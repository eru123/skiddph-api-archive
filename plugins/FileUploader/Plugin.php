<?php

namespace Plugin\FileUploader;

use PluginKey;
use PluginDB;
use PluginConfig;
use Database;

use Api\Database\ORM;

class Plugin implements PluginKey, PluginDB
{
    const TB = 'plugin_file_uploader';
    public static function key(string $key = null): string
    {
        return "FILE_UPLOADER";
    }

    public static function config(): PluginConfig
    {
        return new PluginConfig(self::key());
    }

    public static function db(): ORM
    {
        return Database::connect(self::config()->get('DB_ENV'));
    }

    public static function dir(): string
    {
        return self::config()->get('UPLOAD_DIR');
    }

    public static function tb(): ORM
    {
        return self::db()->clear()->table(self::TB);
    }
}
