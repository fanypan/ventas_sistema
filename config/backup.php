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

    /*
    | Horas locales (America/Asuncion) separadas por coma. Vacío = no programar.
    | En la PC del comercio: horario de atención (la máquina tiene que estar prendida).
    | Ej.: 17:00  |  13:00,19:30  |  02:30 (solo VPS 24/7)
    */
    'schedule' => (string) env('BACKUP_SCHEDULE', '02:30'),
];
