<?php

namespace Tests\Unit;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\Billing\SubscriptionService;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);
    }

    public function test_lifetime_subscription_has_no_end_date(): void
    {
        $tenant = $this->quietTenant();
        $plan = Plan::where('slug', 'onprem')->firstOrFail();
        $service = app(SubscriptionService::class);

        $subscription = $service->start($tenant, $plan, Subscription::INTERVAL_MONTHLY);

        $this->assertTrue($subscription->isLifetime());
        $this->assertNull($subscription->ends_at);
        $this->assertNull($subscription->grace_ends_at);
        $this->assertSame('Sin vencimiento', $subscription->endsLabel());
    }

    public function test_tick_does_not_suspend_lifetime_tenants(): void
    {
        $tenant = $this->quietTenant();
        $plan = Plan::where('slug', 'onprem')->firstOrFail();
        $service = app(SubscriptionService::class);
        $service->start($tenant, $plan, Subscription::INTERVAL_LIFETIME);

        $service->tick(now()->addYears(5));

        $tenant = $tenant->fresh();
        $this->assertSame(Tenant::STATUS_ACTIVE, $tenant->status);
        $this->assertTrue($tenant->subscription->isLifetime());
        $this->assertNull($tenant->subscription->ends_at);
    }

    public function test_monthly_subscription_enters_grace_after_end(): void
    {
        $tenant = $this->quietTenant();
        $plan = Plan::where('slug', 'starter')->firstOrFail();
        $service = app(SubscriptionService::class);
        $service->start($tenant, $plan, Subscription::INTERVAL_MONTHLY);

        $service->tick(now()->addMonth()->addDay());

        $this->assertSame(Tenant::STATUS_GRACE, $tenant->fresh()->status);
    }

    private function quietTenant(): Tenant
    {
        $tenant = new Tenant([
            'name' => 'Onprem Shop',
            'slug' => 'onpremshop',
            'status' => Tenant::STATUS_ACTIVE,
            'plan_id' => Plan::first()->id,
            'admin_name' => 'Admin',
            'admin_email' => 'admin@onprem.test',
            'admin_password_hash' => Hash::make('secret'),
            'provisioned_at' => now(),
        ]);
        $tenant->id = (string) Str::uuid();
        $tenant->saveQuietly();

        return $tenant;
    }
}
