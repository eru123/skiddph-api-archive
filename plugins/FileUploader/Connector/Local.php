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
                $insert_id = Plugin::tb()
                    ->data([
                        [
                            'user_id' => $file['user_id'],
                            'name' => $file['name'],
                            'mime' => $file['mime'],
                            'size' => $file['size'],
                            'hash' => $file['hash'],
                            'date' => $file['date']
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
}
