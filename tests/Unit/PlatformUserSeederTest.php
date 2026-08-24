<?php

namespace Tests\Unit;

use Database\Seeders\PlatformUserSeeder;
use Tests\TestCase;

class PlatformUserSeederTest extends TestCase
{
    public function test_testing_falls_back_to_plataforma(): void
    {
        config(['saas.platform_admin_password' => null]);

        $this->assertSame('plataforma', PlatformUserSeeder::password());
    }

    public function test_configured_password_wins(): void
    {
        config(['saas.platform_admin_password' => 'clave-staff-99']);

        $this->assertSame('clave-staff-99', PlatformUserSeeder::password());
    }

    public function test_production_requires_an_explicit_password(): void
    {
        config(['saas.platform_admin_password' => '']);
        $this->app['env'] = 'production';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('PLATFORM_ADMIN_PASSWORD');

        PlatformUserSeeder::password();
    }
}
