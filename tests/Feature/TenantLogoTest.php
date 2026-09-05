<?php

namespace Tests\Feature;

use App\Helpers\SettingHelper;
use App\Http\Middleware\PreventRequestForgery;
use App\Models\Plan;
use App\Models\PlatformUser;
use App\Models\Tenant;
use App\Services\Media\TenantLogoService;
use App\Support\PlatformAccess;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TenantTestCase;

class TenantLogoTest extends TenantTestCase
{
    public function test_staff_can_set_and_reset_tenant_logo_from_platform(): void
    {
        $path = config('saas.platform_path');
        $file = UploadedFile::fake()->image('marca.png', 200, 80);

        $this->actingAs(PlatformUser::first(), 'platform')
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post("/{$path}/clientes/{$this->tenant->id}/logo", [
                'logo' => $file,
            ])
            ->assertRedirect();

        $stored = $this->tenant->run(fn () => SettingHelper::getValue('app_logo'));
        $this->assertNotNull($stored);
        $this->assertTrue(TenantLogoService::isCustomPath($stored));
        $this->assertStringStartsWith('branding/', $stored);

        $this->tenant->run(function () use ($stored) {
            Storage::disk(config('media.public_disk'))->assertExists($stored);
        });

        $this->actingAs(PlatformUser::first(), 'platform')
            ->get("/{$path}/clientes/{$this->tenant->id}")
            ->assertOk()
            ->assertSee('Guardar logo')
            ->assertSee(route('platform.tenants.logo', $this->tenant), false);

        $this->actingAs(PlatformUser::first(), 'platform')
            ->get("/{$path}/clientes/{$this->tenant->id}/logo")
            ->assertOk()
            ->assertHeader('content-type', 'image/png');

        $this->actingAs(PlatformUser::first(), 'platform')
            ->withoutMiddleware(PreventRequestForgery::class)
            ->delete("/{$path}/clientes/{$this->tenant->id}/logo")
            ->assertRedirect();

        $this->tenant->run(function () use ($stored) {
            $this->assertSame(TenantLogoService::DEFAULT_PATH, SettingHelper::getValue('app_logo'));
            Storage::disk(config('media.public_disk'))->assertMissing($stored);
        });
    }

    public function test_svg_logo_is_rejected(): void
    {
        $path = config('saas.platform_path');
        $svg = UploadedFile::fake()->create('payload.svg', 20, 'image/svg+xml');

        $this->actingAs(PlatformUser::first(), 'platform')
            ->from("/{$path}/clientes/{$this->tenant->id}")
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post("/{$path}/clientes/{$this->tenant->id}/logo", [
                'logo' => $svg,
            ])
            ->assertRedirect("/{$path}/clientes/{$this->tenant->id}")
            ->assertSessionHasErrors('logo');

        $this->tenant->run(function () {
            $this->assertSame('storage/logo.png', SettingHelper::getValue('app_logo'));
        });
    }

    public function test_create_tenant_applies_uploaded_logo(): void
    {
        Mail::fake();

        $path = config('saas.platform_path');
        $file = UploadedFile::fake()->image('alta.jpg', 160, 160);

        $this->actingAs(PlatformUser::first(), 'platform')
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post("/{$path}/clientes", [
                'name' => 'Logo Shop',
                'slug' => 'logoshop',
                'plan_id' => Plan::first()->id,
                'admin_name' => 'Ada',
                'admin_email' => 'ada@logo.test',
                'interval' => 'monthly',
                'logo' => $file,
            ])
            ->assertRedirect();

        $tenant = Tenant::where('slug', 'logoshop')->firstOrFail();
        $this->rememberTenantArtifact($tenant->getTenantKey());

        $this->assertNull($tenant->pending_logo_path);

        $stored = $tenant->run(fn () => SettingHelper::getValue('app_logo'));
        $this->assertTrue(TenantLogoService::isCustomPath($stored));
        $this->assertStringStartsWith('branding/', $stored);
        $tenant->run(function () use ($stored) {
            Storage::disk(config('media.public_disk'))->assertExists($stored);
        });
    }

    public function test_billing_cannot_update_tenant_logo(): void
    {
        $billing = PlatformUser::create([
            'name' => 'Cobros',
            'email' => 'billing-logo@arandutech.com',
            'password' => Hash::make('secret'),
            'role' => PlatformAccess::ROLE_BILLING,
        ]);
        $billing->assignRole(PlatformAccess::ROLE_BILLING);

        $path = config('saas.platform_path');

        $this->actingAs($billing, 'platform')
            ->get("/{$path}/clientes/{$this->tenant->id}")
            ->assertOk()
            ->assertDontSee('Guardar logo');

        $this->actingAs($billing, 'platform')
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post("/{$path}/clientes/{$this->tenant->id}/logo", [
                'logo' => UploadedFile::fake()->image('no.png'),
            ])
            ->assertForbidden();
    }
}
