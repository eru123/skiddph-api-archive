<?php

return [
    /**
     * Maximum File Upload Size in Bytes
     */
    'max_upload_size' => 2097152,
    /**
     * Connector to be use
     * valid values: local|s3bucket
     */
    'connector' => env('FILE_UPLOADER_CONNECTOR', 'local'),
    /**
     * Upload Directory - For Local Storage Connector
     */
    'upload_dir' => __DIR__ . '/uploads',
    /**
     * Time to Live for S3 Bucket URL
     */
    'ttl' => '+20 minutes'
];
