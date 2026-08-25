<?php

namespace Tests\Unit;

use App\Models\Plan;
use App\Models\Tenant;
use App\Services\Tenancy\TenantProvisioningRollback;
use Database\Seeders\PlanSeeder;
use Database\Seeders\TenantDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\CleansTenantArtifacts;
use Tests\TestCase;

class TenantProvisioningRollbackTest extends TestCase
{
    use CleansTenantArtifacts;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'tenancy.central_domains' => ['localhost', '127.0.0.1'],
            'saas.tenant_base_domain' => 'localhost',
            'queue.default' => 'sync',
        ]);

        $this->seed(PlanSeeder::class);
    }

    public function test_rollback_removes_unprovisioned_tenant(): void
    {
        $plan = Plan::first();

        $tenant = new Tenant([
            'name' => 'Rollback Test',
            'slug' => 'rollbacktest',
            'status' => Tenant::STATUS_PENDING,
            'plan_id' => $plan->id,
            'admin_name' => 'Admin',
            'admin_email' => 'admin@rollback.test',
            'admin_password_hash' => Hash::make('secret'),
        ]);
        $tenant->id = (string) Str::uuid();
        $tenant->saveQuietly();
        $this->rememberTenantArtifact($tenant->getTenantKey());

        $storageDir = base_path('storage/tenant'.$tenant->getTenantKey());
        mkdir($storageDir.'/app/public', 0777, true);
        $this->assertDirectoryExists($storageDir);

        app(TenantProvisioningRollback::class)->rollback($tenant);

        $this->assertDatabaseMissing('tenants', ['slug' => 'rollbacktest']);
        $this->assertDirectoryDoesNotExist($storageDir);
    }

    public function test_rollback_skips_provisioned_tenant(): void
    {
        $plan = Plan::first();

        $tenant = new Tenant([
            'name' => 'Provisioned',
            'slug' => 'provisioned',
            'status' => Tenant::STATUS_ACTIVE,
            'plan_id' => $plan->id,
            'admin_name' => 'Admin',
            'admin_email' => 'admin@provisioned.test',
            'admin_password_hash' => Hash::make('secret'),
            'provisioned_at' => now(),
        ]);
        $tenant->id = (string) Str::uuid();
        $tenant->saveQuietly();

        app(TenantProvisioningRollback::class)->rollback($tenant);

        $this->assertDatabaseHas('tenants', ['slug' => 'provisioned']);
    }

    public function test_failed_provisioning_rolls_back_tenant_record(): void
    {
        $this->mock(TenantDatabaseSeeder::class, function ($mock) {
            $mock->shouldReceive('run')->andThrow(new \RuntimeException('Seed failed'));
        });

        $plan = Plan::first();

        try {
            Tenant::create([
                'name' => 'Fail Test',
                'slug' => 'failtest',
                'status' => Tenant::STATUS_PENDING,
                'plan_id' => $plan->id,
                'admin_name' => 'Admin',
                'admin_email' => 'admin@fail.test',
                'admin_password_hash' => Hash::make('secret'),
                'setup_password' => 'secret',
            ]);
        } catch (\RuntimeException) {
            // expected
        }

        $this->assertDatabaseMissing('tenants', ['slug' => 'failtest']);
    }

    protected function tearDown(): void
    {
        $this->cleanupTenantArtifacts();

        parent::tearDown();
    }
}
