<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Support\Env;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return Application
     */
    public function createApplication()
    {
        // php artisan test hereda el env de Docker (pgsql). Los tests deben ir siempre en sqlite.
        foreach ([
            'APP_ENV' => 'testing',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'QUEUE_CONNECTION' => 'sync',
            'CACHE_DRIVER' => 'array',
            'SESSION_DRIVER' => 'array',
            'MAIL_MAILER' => 'array',
            'PLATFORM_PATH' => 'plataforma',
            'CENTRAL_DOMAINS' => 'localhost,127.0.0.1',
            'TRUSTED_PROXIES' => '',
        ] as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        putenv('PLATFORM_DOMAIN');
        unset($_ENV['PLATFORM_DOMAIN'], $_SERVER['PLATFORM_DOMAIN']);
        putenv('PLATFORM_ADMIN_PASSWORD');
        unset($_ENV['PLATFORM_ADMIN_PASSWORD'], $_SERVER['PLATFORM_ADMIN_PASSWORD']);

        $this->configureApplicationEnvironment();

        // phpdotenv deja un repositorio inmutable entre tests; hay que recrearlo
        // para que env() vea PLATFORM_PATH / PLATFORM_DOMAIN del test actual.
        Env::disablePutenv();
        Env::enablePutenv();

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function configureApplicationEnvironment(): void {}
}
