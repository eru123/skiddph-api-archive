<?php

namespace Api\Core;

use Dotenv\Dotenv;

class Bootstrapper
{   
    private static $config_path = null;
    const config_ext = ["php"];
    const config_name = "config";

    public static function load(string $cwd = null): void
    {
        if ($cwd === null) $cwd = getcwd();
        if (is_dir($cwd)) $cwd = realpath($cwd);
        else return;

        $files = scandir($cwd, SCANDIR_SORT_ASCENDING);
        foreach ($files as $file) {
            if (is_file($file)) {
                $file_info = pathinfo($file);
                if (@$file_info["extension"] && in_array($file_info["extension"], self::config_ext) && $file_info["filename"] === self::config_name) {
                    self::$config_path = $cwd . DIRECTORY_SEPARATOR . $file;
                    break;
                }
            }
        }
    }

    public static function init(string $dir = null): void
    {
        if ($dir === null) $dir = getcwd();

        Dotenv::createImmutable($dir)->load();

        $allowed_config_exts = ["php", "json", "inc"];
        $allowed_config_name = "config";

        $files = scandir($dir, SCANDIR_SORT_ASCENDING);
        foreach ($files as $file) {
            if (is_file($file)) {
                $file_info = pathinfo($file);
                if (@$file_info["extension"] && in_array($file_info["extension"], $allowed_config_exts) && $file_info["filename"] === $allowed_config_name) {
                    $config = require_once $dir . DIRECTORY_SEPARATOR . $file;
                    foreach ($config as $key => $value) {
                        $plugin = new PluginConfig($key);
                        foreach ($value as $k => $v) {
                            $plugin->set($k, $v);
                        }
                    }
                    break;
                }
            }
        }
    }

    
}
