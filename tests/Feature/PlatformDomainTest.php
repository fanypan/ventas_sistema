<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformDomainTest extends TestCase
{
    use RefreshDatabase;

    protected function configureApplicationEnvironment(): void
    {
        putenv('PLATFORM_DOMAIN=admin.localhost');
        putenv('CENTRAL_DOMAINS=localhost,127.0.0.1,admin.localhost');
        $_ENV['PLATFORM_DOMAIN'] = 'admin.localhost';
        $_SERVER['PLATFORM_DOMAIN'] = 'admin.localhost';
        $_ENV['CENTRAL_DOMAINS'] = 'localhost,127.0.0.1,admin.localhost';
        $_SERVER['CENTRAL_DOMAINS'] = 'localhost,127.0.0.1,admin.localhost';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);
        $this->seed(\Database\Seeders\PlatformUserSeeder::class);
    }

    public function test_platform_only_on_configured_domain(): void
    {
        $path = config('saas.platform_path');

        $this->get("http://localhost/{$path}/login")->assertNotFound();
        $this->get("http://admin.localhost/{$path}/login")->assertOk();
    }
}
