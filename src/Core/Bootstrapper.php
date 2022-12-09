<?php

namespace App\Core;

use Dotenv\Dotenv;

class Bootstrapper
{
    public static function init(string $dir = null): void
    {
        if ($dir === null) {
            $dir = getcwd();
        }

        if (is_dir($dir)) {
            $dir = realpath($dir);
        }

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
