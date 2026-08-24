<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\PlatformUser;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class PlatformRbacTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        config(['tenancy.central_domains' => ['localhost', '127.0.0.1']]);
        $this->seed(\Database\Seeders\PlanSeeder::class);
        $this->seed(\Database\Seeders\PlatformUserSeeder::class);
        $this->path = config('saas.platform_path');
    }

    public function test_staff_can_use_day_to_day_panel(): void
    {
        $staff = $this->staff();
        $tenant = $this->quietTenant();

        $this->actingAs($staff, 'platform')
            ->get("/{$this->path}")
            ->assertOk();

        $this->actingAs($staff, 'platform')
            ->get("/{$this->path}/clientes")
            ->assertOk()
            ->assertSee('Nuevo cliente');

        $this->actingAs($staff, 'platform')
            ->get("/{$this->path}/clientes/{$tenant->id}")
            ->assertOk()
            ->assertSee('Registrar pago')
            ->assertDontSee('Eliminar')
            ->assertDontSee('>Baja<', false);

        $this->actingAs($staff, 'platform')
            ->get("/{$this->path}/planes")
            ->assertOk()
            ->assertDontSee('Editar');
    }

    public function test_staff_cannot_destroy_cancel_or_edit_plans(): void
    {
        $staff = $this->staff();
        $tenant = $this->quietTenant();
        $plan = Plan::first();

        $this->actingAs($staff, 'platform')
            ->withoutMiddleware(\App\Http\Middleware\PreventRequestForgery::class)
            ->delete("/{$this->path}/clientes/{$tenant->id}")
            ->assertForbidden();

        $this->actingAs($staff, 'platform')
            ->withoutMiddleware(\App\Http\Middleware\PreventRequestForgery::class)
            ->post("/{$this->path}/clientes/{$tenant->id}/baja")
            ->assertForbidden();

        $this->actingAs($staff, 'platform')
            ->get("/{$this->path}/planes/{$plan->id}/editar")
            ->assertForbidden();

        $this->actingAs($staff, 'platform')
            ->withoutMiddleware(\App\Http\Middleware\PreventRequestForgery::class)
            ->put("/{$this->path}/planes/{$plan->id}", [
                'name' => 'Hack',
                'price_monthly' => 1,
                'price_yearly' => 1,
                'max_users' => 1,
                'max_cajas' => 1,
                'sifen_documents_monthly' => 0,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('tenants', ['slug' => 'rbacshop']);
        $this->assertDatabaseHas('plans', ['id' => $plan->id, 'name' => $plan->name]);
    }

    public function test_admin_keeps_destructive_actions(): void
    {
        $admin = PlatformUser::first();
        $tenant = $this->quietTenant();
        $plan = Plan::first();

        $this->actingAs($admin, 'platform')
            ->get("/{$this->path}/clientes/{$tenant->id}")
            ->assertOk()
            ->assertSee('Eliminar')
            ->assertSee('Baja');

        $this->actingAs($admin, 'platform')
            ->get("/{$this->path}/planes")
            ->assertOk()
            ->assertSee('Editar');

        $this->actingAs($admin, 'platform')
            ->get("/{$this->path}/planes/{$plan->id}/editar")
            ->assertOk();
    }

    public function test_session_cookies_are_not_forced_secure_in_tests(): void
    {
        $this->assertFalse((bool) config('session.secure'));
    }

    private function staff(): PlatformUser
    {
        return PlatformUser::create([
            'name' => 'Staff',
            'email' => 'staff@arandutech.com',
            'password' => Hash::make('secret'),
            'role' => PlatformUser::ROLE_STAFF,
        ]);
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
