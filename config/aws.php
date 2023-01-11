<?php

return [
    's3bucket' => [
        'key' => env('S3_KEY'),
        'secret' => env('S3_SECRET'),
        'region' => env('S3_REGION'),
        'version' => env('S3_VERSION'),
        'bucket' => env('S3_BUCKET'),
    ]
];
