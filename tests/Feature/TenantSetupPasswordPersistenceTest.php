<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Tenant;
use App\Support\TenantSetupPassword;
use Database\Seeders\PlanSeeder;
use Database\Seeders\PlatformUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\CleansTenantArtifacts;
use Tests\TestCase;

class TenantSetupPasswordPersistenceTest extends TestCase
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

        $this->seed([
            PlanSeeder::class,
            PlatformUserSeeder::class,
        ]);
    }

    public function test_setup_password_never_lands_in_tenant_data_column(): void
    {
        $plan = Plan::first();
        $plain = 'VisiblePlaintext-7K';

        $tenant = Tenant::create([
            'name' => 'Sin plaintext',
            'slug' => 'sinplaintext',
            'status' => Tenant::STATUS_PENDING,
            'plan_id' => $plan->id,
            'admin_name' => 'Admin',
            'admin_email' => 'admin@sin-plaintext.test',
            'admin_password_hash' => Hash::make($plain),
            'setup_password' => $plain,
        ]);
        $this->rememberTenantArtifact($tenant->getTenantKey());

        $this->assertNull($tenant->fresh()->setup_password);
        $this->assertNull(TenantSetupPassword::pull($tenant->getTenantKey()));

        $raw = DB::table('tenants')->where('id', $tenant->id)->value('data');
        $this->assertStringNotContainsString($plain, (string) $raw);
    }

    protected function tearDown(): void
    {
        $this->cleanupTenantArtifacts();

        parent::tearDown();
    }
}
