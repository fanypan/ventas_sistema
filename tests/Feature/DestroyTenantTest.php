<?php

namespace Tests\Feature;

use App\Http\Middleware\PreventRequestForgery;
use App\Models\Plan;
use App\Models\PlatformUser;
use App\Models\Tenant;
use Database\Seeders\PlanSeeder;
use Database\Seeders\PlatformUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class DestroyTenantTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        config(['tenancy.central_domains' => ['localhost', '127.0.0.1']]);
        $this->seed(PlanSeeder::class);
        $this->seed(PlatformUserSeeder::class);
        $this->path = config('saas.platform_path');
    }

    public function test_admin_must_confirm_with_password(): void
    {
        $admin = PlatformUser::first();
        $tenant = $this->quietTenant();

        $this->actingAs($admin, 'platform')
            ->from("/{$this->path}/clientes/{$tenant->id}")
            ->withoutMiddleware(PreventRequestForgery::class)
            ->delete("/{$this->path}/clientes/{$tenant->id}")
            ->assertRedirect("/{$this->path}/clientes/{$tenant->id}")
            ->assertSessionHasErrors('password');

        $this->assertDatabaseHas('tenants', ['slug' => 'rbacshop']);
    }

    public function test_admin_cannot_destroy_with_wrong_password(): void
    {
        $admin = PlatformUser::first();
        $tenant = $this->quietTenant();

        $this->actingAs($admin, 'platform')
            ->from("/{$this->path}/clientes/{$tenant->id}")
            ->withoutMiddleware(PreventRequestForgery::class)
            ->delete("/{$this->path}/clientes/{$tenant->id}", [
                'password' => 'incorrecta',
            ])
            ->assertRedirect("/{$this->path}/clientes/{$tenant->id}")
            ->assertSessionHasErrors('password');

        $this->assertDatabaseHas('tenants', ['slug' => 'rbacshop']);
    }

    public function test_admin_destroys_tenant_with_correct_password(): void
    {
        $admin = PlatformUser::first();
        $tenant = $this->quietTenant();

        $this->actingAs($admin, 'platform')
            ->withoutMiddleware(PreventRequestForgery::class)
            ->delete("/{$this->path}/clientes/{$tenant->id}", [
                'password' => PlatformUserSeeder::password(),
            ])
            ->assertRedirect(route('platform.tenants.index'));

        $this->assertDatabaseMissing('tenants', ['slug' => 'rbacshop']);
    }

    public function test_show_page_asks_for_password_before_delete(): void
    {
        $admin = PlatformUser::first();
        $tenant = $this->quietTenant();

        $this->actingAs($admin, 'platform')
            ->get("/{$this->path}/clientes/{$tenant->id}")
            ->assertOk()
            ->assertSee('¿Eliminar este cliente?')
            ->assertSee('Tu contraseña de plataforma')
            ->assertSee('delete-tenant-form', false);
    }

    private function quietTenant(): Tenant
    {
        $tenant = new Tenant([
            'name' => 'RBAC Shop',
            'slug' => 'rbacshop',
            'status' => Tenant::STATUS_ACTIVE,
            'plan_id' => Plan::first()->id,
            'admin_name' => 'Admin',
            'admin_email' => 'admin@rbac.test',
            'admin_password_hash' => Hash::make('secret'),
            'provisioned_at' => now(),
        ]);
        $tenant->id = (string) Str::uuid();
        $tenant->saveQuietly();

        return $tenant;
    }
}
