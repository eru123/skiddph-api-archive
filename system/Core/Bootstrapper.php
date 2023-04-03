<?php

namespace SkiddPH\Core;

use Dotenv\Dotenv;

class Bootstrapper
{
    static $initiated = false;
    const config_ext = ["php"];
    const config_name = "config";
    const config_dir = "config";

    public static function init(string $dir = null): void
    {
        if (self::$initiated)
            return;
        self::$initiated = true;

        $data = [];

        if ($dir === null)
            $dir = getcwd();
        if (is_dir($dir))
            $dir = realpath($dir);
        else
            return;

        if (file_exists(rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.env')) {
            Dotenv::createImmutable($dir)->load();
        }
        
        $files = scandir($dir, SCANDIR_SORT_ASCENDING);
        foreach ($files as $file) {
            if (file_exists($file)) {
                $finfo = pathinfo($file);
                if (
                    @$finfo["extension"] &&
                    in_array($finfo["extension"], self::config_ext) &&
                    $finfo["filename"] === self::config_name &&
                    file_exists($dir . DIRECTORY_SEPARATOR . $file)
                ) {
                    $fdata = require($dir . DIRECTORY_SEPARATOR . $file);
                    $fdata = is_array($data) ? $data : [];
                    $data = array_merge($data, $fdata);
                    sys('plugins', $data);
                    $fdata = null;
                    $data = null;
                    $finfo = null;
                    $file = null;
                    $files = null;
                    break;
                }
            }
        }

        $config_dir = $dir . DIRECTORY_SEPARATOR . self::config_dir;
        $pre = [];
        $post = [];
        $on = [];

        // System Specifics
        sys('env', @$_ENV ?? []);
        sys('session', @$_SESSION ?? []);
        sys('server', @$_SERVER ?? []);
        sys('request', @$_REQUEST ?? []);
        sys('cookie', @$_COOKIE ?? []);
        sys('get', @$_GET ?? []);
        sys('post', @$_POST ?? []);
        sys('files', @$_FILES ?? []);
        sys('workdir', $dir);
        sys('configdir', $config_dir);

        if (is_dir($config_dir)) {
            $files = scandir($config_dir, SCANDIR_SORT_ASCENDING);
            foreach ($files as $file) {
                $file = $config_dir . DIRECTORY_SEPARATOR . $file;
                if (file_exists($file)) {
                    $finfo = pathinfo($file);
                    if (
                        @$finfo["extension"] &&
                        in_array($finfo["extension"], self::config_ext) &&
                        file_exists($file)
                    ) {
                        if (preg_match('/^(.*)\.pre\.php$/', $finfo["basename"], $matches)) {
                            $pre[$matches[1]] = $file;
                        } else if (preg_match('/^(.*)\.post\.php$/', $finfo["basename"], $matches)) {
                            $post[$matches[1]] = $file;
                        } else if (preg_match('/^(.*)\.php$/', $finfo["basename"], $matches)) {
                            $on[$matches[1]] = $file;
                        }
                    }
                }
            }
        }

        foreach ($pre as $key => $file) {
            $fdata = require($file);
            sys("plugins.$key", $fdata);
            if ($key === 'app') {
                sys('timezone', pcfg('app.timezone'));
                datetime_init();
            }
        }

        if (!pcfg('app.timezone')) {
            datetime_init();
        }

        foreach ($on as $key => $file) {
            $fdata = require($file);
            sys("plugins.$key", $fdata);
        }

        foreach ($post as $key => $file) {
            $fdata = require($file);
            sys("plugins.$key", $fdata);
        }

        $on = null;
        $pre = null;
        $post = null;
        $files = null;
        $key = null;
        $file = null;
    }

    public static function phinxMigrationPaths(): array {
        $class_map = require workdir() . '/vendor/composer/autoload_classmap.php';
        $migrations = [
            workdir() . '/db/migrations'
        ];

        foreach ($class_map as $class => $path) {
            $classarr = explode('\\', $class);
            if (end($classarr) === 'Phinx' && method_exists($class, 'migrations')) {
                $external = call_user_func([$class, 'migrations']);
                if (is_array($external)) {
                    $migrations = array_merge($migrations, $external);
                } else {
                    $migrations[] = $external;
                }
            }
        }

        foreach ($migrations as $key => $path) {
            $migrations[$key] = realpath($path);
            if (!$migrations[$key]) {
                unset($migrations[$key]);
            }
        }

        return array_unique($migrations);
    }
}