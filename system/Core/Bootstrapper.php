<?php

namespace SkiddPH\Core;

use Dotenv\Dotenv;
use SkiddPH\Core\Plugin\Config as PluginConfig;

class Bootstrapper
{
    private static $config_path = null;
    const config_ext = ["php"];
    const config_name = "config";

    public static function load(string $cwd = null): void
    {
        if ($cwd === null)
            $cwd = getcwd();
        if (is_dir($cwd))
            $cwd = realpath($cwd);
        else
            return;

        Dotenv::createImmutable($cwd)->load();
        $files = scandir($cwd, SCANDIR_SORT_ASCENDING);
        foreach ($files as $file) {
            if (file_exists($file)) {
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
        if ($dir) {
            self::load($dir);
        }

        $cfg_file = self::$config_path;

        if (!empty($cfg_file) && file_exists($cfg_file)) {
            $config = require $cfg_file;
            foreach ($config as $key => $value) {
                $plugin = new PluginConfig($key);
                foreach ($value as $k => $v) {
                    $plugin->set($k, $v);
                }
            }
        }
    }
}