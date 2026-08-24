<?php

use Laravel\Telescope\Telescope;

return [
    /*
    | Sentry se apaga solo dejando SENTRY_LARAVEL_DSN vacío.
    | Horizon / Telescope no se registran si el flag es false (además Telescope es require-dev).
    */
    'horizon_enabled' => filter_var(env('HORIZON_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    'telescope_enabled' => filter_var(env('TELESCOPE_ENABLED', false), FILTER_VALIDATE_BOOLEAN)
        && env('APP_ENV') === 'local'
        && class_exists(Telescope::class),

    'health_enabled' => filter_var(env('HEALTH_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    'health_path' => preg_match('/^[a-z0-9_-]+$/i', trim((string) env('HEALTH_PATH', 'up'), '/'))
        ? trim((string) env('HEALTH_PATH', 'up'), '/')
        : 'up',

    'health_check_redis' => filter_var(env('HEALTH_CHECK_REDIS', true), FILTER_VALIDATE_BOOLEAN),
];
