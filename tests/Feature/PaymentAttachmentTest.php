<?php

namespace Tests\Feature;

use App\Http\Middleware\PreventRequestForgery;
use App\Models\Plan;
use App\Models\PlatformUser;
use App\Models\Tenant;
use Database\Seeders\PlanSeeder;
use Database\Seeders\PlatformUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\CleansTenantArtifacts;
use Tests\TestCase;

class PaymentAttachmentTest extends TestCase
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
            'media.payment_disk' => 'local',
        ]);

        $this->seed([
            PlanSeeder::class,
            PlatformUserSeeder::class,
        ]);
    }

    public function test_staff_can_upload_and_download_payment_receipt(): void
    {
        $plan = Plan::first();
        $tenant = Tenant::create([
            'name' => 'Pago Shop',
            'slug' => 'pagoshop',
            'status' => Tenant::STATUS_PENDING,
            'plan_id' => $plan->id,
            'admin_name' => 'Admin',
            'admin_email' => 'admin@pago.test',
            'admin_password_hash' => Hash::make('password'),
            'setup_password' => 'password',
        ]);
        $this->rememberTenantArtifact($tenant->getTenantKey());

        Storage::fake('local');

        $path = config('saas.platform_path');
        $file = UploadedFile::fake()->create('recibo.pdf', 20, 'application/pdf');

        $this->actingAs(PlatformUser::first(), 'platform')
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post("/{$path}/clientes/{$tenant->id}/pago", [
                'amount' => 150000,
                'method' => 'transferencia',
                'paid_at' => now()->toDateString(),
                'interval' => 'monthly',
                'attachment' => $file,
            ])
            ->assertRedirect(route('platform.tenants.show', $tenant));

        $tenant->refresh();
        $payment = $tenant->payments()->first();
        $this->assertNotNull($payment);
        $this->assertNotNull($payment->attachment_path);
        Storage::disk('local')->assertExists($payment->attachment_path);

        $this->actingAs(PlatformUser::first(), 'platform')
            ->get("/{$path}/clientes/{$tenant->id}/pagos/{$payment->id}/comprobante")
            ->assertOk();
    }

    protected function tearDown(): void
    {
        $this->cleanupTenantArtifacts();

        parent::tearDown();
    }
}
