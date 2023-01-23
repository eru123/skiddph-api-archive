<?php

return [
    /**
     * Maximum File Upload Size in Bytes
     */
    'max_upload_size' => (int) e('FILE_UPLOADER_MAX_UPLOAD_SIZE', 2097152 /** 2MB */),
    /**
     * Connector to be use
     * valid values: local|s3bucket
     */
    'connector' => e('FILE_UPLOADER_CONNECTOR', 'local'),
    /**
     * Upload Directory - For Local Storage Connector
     */
    'upload_dir' => workdir() . '/uploads',
    /**
     * Time to Live for S3 Bucket URL
     */
    'ttl' => '+20 minutes'
];
