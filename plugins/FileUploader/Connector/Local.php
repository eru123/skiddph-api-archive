<?php

namespace Plugin\FileUploader\Connector;

use Exception;
use Plugin\FileUploader\Plugin;
use Api\Lib\Date;
use Api\Lib\File;

class Local
{
    public static function upload($files)
    {
        $orm = Plugin::db();
        $upload_dir = File::autodir(Plugin::dir());
        $res = [];

        try {
            $orm->begin();

            foreach ($files as $file) {
                $size = (int) $file['size'];
                if (Plugin::maxFileSize() < $size) {
                    throw new Exception("File size is too large", 400);
                }

                $insert_id = Plugin::tb()
                    ->data([
                        [
                            'user_id' => $file['user_id'],
                            'name' => $file['name'],
                            'mime' => $file['mime'],
                            'size' => $file['size'],
                            'hash' => $file['hash'],
                            'date' => $file['date'],
                            'connector' => 'local'
                        ]
                    ])
                    ->insert()
                    ->lastInsertId();

                $file_ext = File::ext($file['name']);
                $now = Date::parse('now', 's');
                $key = $now . $insert_id;
                $name = $key . '.' . $file_ext;
                $path = $upload_dir . '/' . $name;
                if (!move_uploaded_file($file['tmp_name'], $path)) {
                    throw new Exception("Error uploading file", 500);
                }

                $add_file = Plugin::tb()
                    ->data([['path' => $name]])
                    ->where(['id' => $insert_id])
                    ->update()
                    ->rowCount() > 0;

                if (!$add_file) {
                    throw new Exception("Failed to save file in Database", 500);
                }

                $res[] = $insert_id;
            }

            $orm->commit();
        } catch (Exception $e) {
            $orm->rollback();
            throw $e;
        }

        return $res;
    }

    public static function stream($file)
    {
        $path = File::autodir(Plugin::dir()) . '/' . $file['path'];
        $mime = $file['mime'];
        $name = $file['name'];
        $size = $file['size'];

        if (!file_exists($path)) {
            throw new Exception("File not found", 404);
        }

        $fp = fopen($path, 'rb');

        header("Content-Type: $mime");
        header("Content-Disposition: attachment; filename=\"$name\"");
        header("Content-Length: $size");

        fpassthru($fp);
        exit;
    }

    public static function download($file)
    {
        $path = File::autodir(Plugin::dir()) . '/' . $file['path'];
        $name = $file['name'];
        $size = $file['size'];

        if (!file_exists($path)) {
            throw new Exception("File not found", 404);
        }

        $fp = fopen($path, 'rb');

        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"$name\"");
        header("Content-Length: $size");
        
        fpassthru($fp);
        exit;
    }
}
