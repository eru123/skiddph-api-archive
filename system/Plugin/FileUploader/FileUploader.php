<?php

namespace SkiddPH\Plugin\FileUploader;

use Exception;
use SkiddPH\Helper\Date;
use SkiddPH\Plugin\Database\ORM;
use SkiddPH\Plugin\Database\Database;
use SkiddPH\Plugin\Auth\Auth;

class FileUploader
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

    public static function db(): ORM
    {
        return Database::connect();
    }

    public static function tb(): ORM
    {
        return self::db()->clear()->table(self::TB);
    }

    /**
     * Summary of getConnector
     * @return Connector\Local|Connector\S3Bucket
     */
    public static function getConnector()
    {
        return self::CONNECTORS[pcfg('fileuploader.connector')];
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
                    $file_obj['hash'] = hash_file('sha256', $file_obj['tmp_name']);
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

    /**
     * Summary of Files
     * @param mixed $id
     * @param mixed $query
     * @return array
     */
    public static function files($id, $query)
    {
        $limit = $query['limit'];
        $marker = $query['marker'];
        $order = $query['order'];
        $mime = $query['mime'];
        $mime = str_replace('*', '%', $mime);

        $q = self::tb()
            ->where(['user_id' => $id])
            ->and()
            ->where(['mime' => ['like' => self::db()->quote($mime)]]);

        if (!empty($marker)) {
            $q->where(['date' => ($order == 'asc' ? ['>' => $marker] : ['<' => $marker])]);
        }

        return $q->limit($limit)
            ->order('date', $order)
            ->readMany()
            ->omit(['user_id', 'connector', 'path'])
            ->arr();
    }

    public static function marker($files) {
        if (count($files) == 0) {
            return '';
        }

        return @end($files)['date'] ?? '';
    }
}
