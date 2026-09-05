<?php

return [
    /*
    | Subcarpeta de storage/app para dumps fechados (la que monta Docker).
    */
    'directory' => env('BACKUP_DIRECTORY', 'backups'),

    /*
    | Días de dumps fechados a conservar. 0 = no borrar nada.
    */
    'keep_days' => (int) env('BACKUP_KEEP_DAYS', 14),

    'compress' => filter_var(env('BACKUP_COMPRESS', true), FILTER_VALIDATE_BOOLEAN),
];
