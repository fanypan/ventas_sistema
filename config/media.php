<?php

return [

    /*
    | Disco público de media del comercio (fotos de producto, branding público).
    | En Docker: minio. En tests: public (disco local sufijado por tenant).
    */
    'public_disk' => env('MEDIA_PUBLIC_DISK', 'public'),

    /*
    | Disco privado del comercio (gestor de archivos). El nombre se mantiene
    | `filemanager` para el paquete alexusmai; el driver puede ser local o s3.
    */
    'private_disk' => env('MEDIA_PRIVATE_DISK', 'filemanager'),

    /*
    | Comprobantes de la plataforma (contexto central, sin prefijo de tenant).
    */
    'payment_disk' => env('MEDIA_PAYMENT_DISK', 'minio_private'),

    'max_image_edge' => (int) env('MEDIA_MAX_IMAGE_EDGE', 1000),

    'jpeg_quality' => (int) env('MEDIA_JPEG_QUALITY', 80),

    /*
    | Endpoint interno (PHP → MinIO) vs el que ve el browser.
    | En Docker local: http://minio:9000 vs http://localhost:9000
    */
    'internal_endpoint' => env('AWS_ENDPOINT'),

    'public_endpoint' => env('AWS_PUBLIC_ENDPOINT', env('AWS_ENDPOINT')),

    'placeholder' => 'images/product-placeholder.svg',

    'logo_max_edge' => (int) env('MEDIA_LOGO_MAX_EDGE', 800),

    /*
    | Discos S3 cuyo prefijo tenant{id}/ hay que borrar al dar de baja.
    */
    'tenant_object_disks' => ['minio', 'minio_private'],
];
