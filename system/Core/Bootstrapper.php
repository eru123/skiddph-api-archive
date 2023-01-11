<?php

namespace SkiddPH\Core;

use Dotenv\Dotenv;
use SkiddPH\Helper\Date;

class Bootstrapper
{
    const config_ext = ["php"];
    const config_name = "config";
    const config_dir = "config";

    public static function init(string $dir = null): void
    {
        $data = [];

        if ($dir === null)
            $dir = getcwd();
        if (is_dir($dir))
            $dir = realpath($dir);
        else
            return;

        Dotenv::createImmutable($dir)->load();
        $files = scandir($dir, SCANDIR_SORT_ASCENDING);
        foreach ($files as $file) {
            if (file_exists($file)) {
                $file_info = pathinfo($file);
                if (
                    @$file_info["extension"] &&
                    in_array($file_info["extension"], self::config_ext) &&
                    $file_info["filename"] === self::config_name &&
                    file_exists($dir . DIRECTORY_SEPARATOR . $file)
                ) {
                    $data = require($dir . DIRECTORY_SEPARATOR . $file);
                    $data = is_array($data) ? $data : [];
                    $data = array_merge($data, $data);
                    break;
                }
            }
        }

        $config_dir = $dir . DIRECTORY_SEPARATOR . self::config_dir;
        if (is_dir($config_dir)) {
            $files = scandir($config_dir, SCANDIR_SORT_ASCENDING);
            foreach ($files as $file) {
                $file = $config_dir . DIRECTORY_SEPARATOR . $file;
                if (file_exists($file)) {
                    $file_info = pathinfo($file);
                    if (
                        @$file_info["extension"] &&
                        in_array($file_info["extension"], self::config_ext) &&
                        file_exists($file)
                    ) {
                        $data[$file_info["filename"]] = require($file);
                    }
                }
            }
        }

        sys('plugins', $data);
        sys('timezone', pcfg('app.timezone', date_default_timezone_get()));

        // Set Timezone
        date_default_timezone_set(sys('timezone'));

        // Set Date and Time
        sys('time', time());
        sys('date', date('Y-m-d H:i:s', sys('time')));
        Date::setTime(sys('time'));

        // System Specifics
        sys('env', @$_ENV ?? []);
        sys('session', @$_SESSION ?? []);
        sys('server', @$_SERVER ?? []);
        sys('request', @$_REQUEST ?? []);
        sys('cookie', @$_COOKIE ?? []);
        sys('get', @$_GET ?? []);
        sys('post', @$_POST ?? []);
        sys('files', @$_FILES ?? []);
    }
}