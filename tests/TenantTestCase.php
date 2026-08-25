<?php

namespace Tests;

use App\Http\Middleware\PreventRequestForgery;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\PlatformUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

abstract class TenantTestCase extends TestCase
{
    use CleansTenantArtifacts;
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $tenantUser;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'tenancy.central_domains' => ['localhost', '127.0.0.1'],
            'saas.tenant_base_domain' => 'localhost',
            'queue.default' => 'sync',
        ]);

        $this->seed([
            PlanSeeder::class,
            PlatformUserSeeder::class,
        ]);

        $plan = Plan::where('slug', 'negocio')->first() ?? Plan::first();

        $this->tenant = Tenant::create([
            'name' => 'Comercio Demo',
            'slug' => 'demo',
            'status' => Tenant::STATUS_PENDING,
            'plan_id' => $plan->id,
            'admin_name' => 'Admin Demo',
            'admin_email' => 'admin@demo.test',
            'admin_password_hash' => Hash::make('password'),
            'setup_password' => 'password',
        ]);

        $this->rememberTenantArtifact($this->tenant->getTenantKey());

        $this->tenantUser = $this->tenant->run(function () {
            return User::where('email', 'admin@demo.test')->firstOrFail();
        });
    }

    protected function tenantGet(string $uri)
    {
        return $this->get('http://demo.localhost'.$uri);
    }

    protected function tenantPost(string $uri, array $data = [])
    {
        return $this->withoutMiddleware(PreventRequestForgery::class)
            ->post('http://demo.localhost'.$uri, $data);
    }

    protected function tearDown(): void
    {
        $tenantId = isset($this->tenant) ? $this->tenant->getTenantKey() : null;
        $this->cleanupTenantArtifacts($tenantId);

        parent::tearDown();
    }
}
