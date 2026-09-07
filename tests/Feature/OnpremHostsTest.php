<?php

namespace Tests\Feature;

use Database\Seeders\PlanSeeder;
use Database\Seeders\PlatformUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnpremHostsTest extends TestCase
{
    use RefreshDatabase;

    protected function configureApplicationEnvironment(): void
    {
        putenv('CENTRAL_DOMAINS=arandutech.com.py,www.arandutech.com.py,admin.arandutech.com.py');
        putenv('TENANT_BASE_DOMAIN=arandutech.com.py');
        putenv('PLATFORM_DOMAIN=admin.arandutech.com.py');
        $_ENV['CENTRAL_DOMAINS'] = 'arandutech.com.py,www.arandutech.com.py,admin.arandutech.com.py';
        $_SERVER['CENTRAL_DOMAINS'] = 'arandutech.com.py,www.arandutech.com.py,admin.arandutech.com.py';
        $_ENV['TENANT_BASE_DOMAIN'] = 'arandutech.com.py';
        $_SERVER['TENANT_BASE_DOMAIN'] = 'arandutech.com.py';
        $_ENV['PLATFORM_DOMAIN'] = 'admin.arandutech.com.py';
        $_SERVER['PLATFORM_DOMAIN'] = 'admin.arandutech.com.py';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        $this->seed(PlatformUserSeeder::class);
    }

    public function test_staff_and_landing_work_when_app_url_is_still_localhost(): void
    {
        $this->assertStringContainsString('localhost', (string) parse_url((string) config('app.url'), PHP_URL_HOST));

        $path = config('saas.platform_path');

        $this->get('http://arandutech.com.py/')->assertOk();
        $this->get("http://admin.arandutech.com.py/{$path}/login")->assertOk();
    }

    public function test_localhost_is_not_the_shop_or_staff_host(): void
    {
        $path = config('saas.platform_path');

        $this->get('http://localhost/')->assertNotFound();
        $this->get("http://localhost/{$path}/login")->assertNotFound();
    }

    public function test_unknown_pos_host_is_not_found_until_tenant_exists(): void
    {
        $this->get('http://cliente.arandutech.com.py/')->assertNotFound();
    }

    public function test_health_accepts_loopback_host(): void
    {
        $this->get('http://127.0.0.1/up')
            ->assertOk()
            ->assertJsonPath('status', 'success');
    }
}
