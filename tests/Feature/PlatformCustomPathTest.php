<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformCustomPathTest extends TestCase
{
    use RefreshDatabase;

    protected function configureApplicationEnvironment(): void
    {
        putenv('PLATFORM_PATH=staff-secret');
        $_ENV['PLATFORM_PATH'] = 'staff-secret';
        $_SERVER['PLATFORM_PATH'] = 'staff-secret';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);
        $this->seed(\Database\Seeders\PlatformUserSeeder::class);
    }

    public function test_custom_platform_path_blocks_legacy_url(): void
    {
        $this->assertSame('staff-secret', config('saas.platform_path'));
        $this->get('/staff-secret/login')->assertOk();
        $this->get('/plataforma/login')->assertNotFound();
    }
}
