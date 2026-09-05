<?php

$s3Disk = static function (string $bucket, bool $public): array {
    return [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'bucket' => $bucket,
        'url' => $public ? env('AWS_URL') : env('AWS_PRIVATE_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
        'use_path_style_endpoint' => filter_var(env('AWS_USE_PATH_STYLE_ENDPOINT', false), FILTER_VALIDATE_BOOLEAN),
        'visibility' => $public ? 'public' : 'private',
        'throw' => false,
        'report' => false,
        'root' => '',
    ];
};

$filemanager = env('FILEMANAGER_DRIVER', 'local') === 's3'
    ? $s3Disk((string) env('AWS_PRIVATE_BUCKET'), false)
    : [
        'driver' => 'local',
        'root' => storage_path('app/file-manager'),
        'throw' => false,
        'report' => false,
    ];

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
            'report' => false,
        ],

        'filemanager' => $filemanager,

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        'minio' => $s3Disk((string) env('AWS_BUCKET', 'ventas-public'), true),

        'minio_private' => $s3Disk((string) env('AWS_PRIVATE_BUCKET', 'ventas-private'), false),

        's3' => $s3Disk((string) env('AWS_BUCKET'), true),

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
