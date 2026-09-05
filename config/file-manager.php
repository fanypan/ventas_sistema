<?php

use Alexusmai\LaravelFileManager\Services\ConfigService\DefaultConfigRepository;
use App\Services\FileManager\TenantFileManagerAclRepository;
use App\Support\TenantMiddleware;

return [

    'configRepository' => DefaultConfigRepository::class,

    'aclRepository' => TenantFileManagerAclRepository::class,

    'routePrefix' => 'file-manager',

    /*
    | Disco dedicado (storage/app/file-manager o bucket privado, sufijado por tenant).
    | Nunca 'local': ahí están payment-receipts y backups centrales.
    | Nunca 'minio_private' suelto: ese disco no se prefija (comprobantes de plataforma).
    */
    'diskList' => ['filemanager'],

    'leftDisk' => 'filemanager',

    'rightDisk' => null,

    'leftPath' => null,

    'rightPath' => null,

    'cache' => null,

    'windowsConfig' => 2,

    'maxUploadFileSize' => 4096,

    'allowFileTypes' => [
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv',
    ],

    'hiddenFiles' => true,

    'middleware' => array_merge(TenantMiddleware::web(), [
        'auth',
        'permission:filemanager',
    ]),

    'acl' => true,

    'aclHideFromFM' => true,

    'aclStrategy' => 'whitelist',

    /*
    | No cachear reglas: la clave del paquete es class+userId, sin tenant.
    */
    'aclRulesCache' => null,

    'aclRules' => [
        null => [],
    ],
];
