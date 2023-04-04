<?php

namespace eru123\orm\ORM;

use Error;

trait Composer
{
    use Base;
    use Model;

    /**
     * Get all models from specified namespace prefix
     * @param array $prefixes
     * @param string $path Vendor path
     * @return array
     */
    public static function get_composer_models(array $prefixes, $vendor_path = null): array
    {   
        $vendor_path = realpath(function_exists('workdir') ? workdir() : $vendor_path);
        $class_map_file = $vendor_path . '/vendor/composer/autoload_classmap.php';
        $models = [];

        if (!file_exists($class_map_file)) {
            throw new Error("Invalid vendor path: '$vendor_path'", 500);
        }

        $class_map = require $class_map_file;
        if (!is_array($prefixes)) {
            $prefixes = [$prefixes];
        }

        foreach ($class_map as $classname => $path) {
            $match = false;
            foreach ($prefixes as $prefix) {
                if (strpos($classname, $prefix) === 0) {
                    $match = true;
                    break;
                }
            }

            if ($match && is_subclass_of($classname, 'eru123\orm\Model')) {
                $models[] = $classname;
            }
        }

        return $models;
    }

    /**
     * Create ORM Collections
     * @param array $prefixes
     * @param string $path Vendor path
     */
    public static function createFromComposer(array $prefixes, $vendor_path = null)
    {
        $models = self::get_composer_models($prefixes, $vendor_path);

        if(!function_exists('pcfg')) {
            throw new Error("Cannot create ORM Collections because 'pcfg' function is not defined.", 500);
        }

        $key = pcfg('database.database', 'default');
        $cfg = pcfg("database.databases.$key", null);

        if (is_null($cfg)) {
            throw new Error("Cannot create ORM Collections because database configuration is invalid.", 500);
        }

        $orm = static::create(...$cfg);
        foreach ($models as $model) {
            $instance = new $model($orm);
            $orm->add($instance->name(), $model);
        }

        return $orm;
    }
}