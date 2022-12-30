<?php

namespace Plugin\FileUploader\Connector;

use Exception;
use Plugin\FileUploader\Plugin;
use Api\Lib\Date;
use Api\Lib\File;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class S3Bucket
{
    public static function factory()
    {
        $s3 = null;
        try {
            $cfg = Plugin::s3BucketConfig();
            $s3 = new S3Client([
                'version' => 'latest',
                'region' => $cfg['region'],
                'credentials' => [
                    'key' => $cfg['key'],
                    'secret' => $cfg['secret']
                ]
            ]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }

        return $s3;
    }

    public static function upload($files)
    {
        $orm = Plugin::db();
        $bucket = Plugin::s3BucketConfig()['bucket'];
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

                $s3 = self::factory();
                $s3->putObject([
                    'Bucket' => $bucket,
                    'Key' => $name,
                    'Body' => fopen($file['tmp_name'], 'r')
                ]);

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
        } catch (S3Exception $e) {
            $orm->rollback();
            throw new Exception($e->getMessage(), 500);
        } catch (Exception $e) {
            $orm->rollback();
            throw $e;
        }

        return $res;
    }

    public static function stream($file)
    {
        $name = $file['path'];
        $bucket = Plugin::s3BucketConfig()['bucket'];
        $ttl = Plugin::s3BucketConfig()['ttl'];
        $s3 = self::factory();
        $cmd = $s3->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $name
        ]);
        $request = $s3->createPresignedRequest($cmd, $ttl);
        $url = (string) $request->getUri();
        header("Location: $url");
        exit;
    }

    public static function download($file)
    {
        return self::stream($file);
    }
}
