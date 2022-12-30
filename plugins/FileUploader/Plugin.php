<?php

namespace Plugin\FileUploader;

use PluginKey;
use PluginDB;
use PluginConfig;
use Database;
use Auth;
use Exception;

use Api\Lib\Date;
use Api\Database\ORM;

class Plugin implements PluginKey, PluginDB
{
    const TB = 'plugin_file_uploader';
    const CONNECTORS = [
        'local' => Connector\Local::class,
        's3bucket' => Connector\S3Bucket::class
    ];

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

    public static function s3BucketConfig(): array
    {
        return self::config()->get('S3_BUCKET');
    }

    public static function getConnector(): string
    {
        $con = self::config()->get('CONNECTOR');
        return self::CONNECTORS[$con];
    }

    public static function maxFileSize(): int
    {
        return (int) self::config()->get('MAX_FILE_SIZE');
    }

    public static function upload()
    {
        $user = Auth::user();

        if (empty($_FILES)) {
            throw new Exception("No file uploaded");
        }

        $file_objs = [];
        foreach ($_FILES as $file) {
            if (!is_array($file['error'])) {
                foreach ($file as $key => $value) {
                    $file[$key] = [$value];
                }
            }
            foreach ($file['error'] as $i => $error) {
                $file_obj = [];
                if ($error === UPLOAD_ERR_OK) {
                    $file_obj['name'] = $file['name'][$i];
                    $file_obj['mime'] = $file['type'][$i];
                    $file_obj['tmp_name'] = $file['tmp_name'][$i];
                    $file_obj['error'] = $file['error'][$i];
                    $file_obj['size'] = $file['size'][$i];
                    $file_obj['full_path'] = @$file['full_path'][$i];
                    $file_obj['user_id'] = $user['id'];
                    $file_obj['date'] = Date::parse('now', 'datetime');
                    $file_obj['hash'] = md5_file($file_obj['tmp_name']);
                    $file_objs[] = $file_obj;
                } else {
                    throw new Exception("Error uploading file");
                }
            }
        }

        $connector = self::getConnector();
        return $connector::upload($file_objs);
    }

    public static function stream($id)
    {
        $file = self::tb()
            ->where(['id' => $id])
            ->readOne()
            ->arr();

        if (empty($file)) {
            throw new Exception("File not found", 404);
        }

        $connector = self::getConnector();
        return $connector::stream($file);
    }

    public static function download($id)
    {
        $file = self::tb()
            ->where(['id' => $id])
            ->readOne()
            ->arr();

        if (empty($file)) {
            throw new Exception("File not found", 404);
        }

        $connector = self::getConnector();
        return $connector::download($file);
    }
}
