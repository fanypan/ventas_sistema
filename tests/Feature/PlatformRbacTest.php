<?php

namespace Tests\Feature;

use App\Http\Middleware\PreventRequestForgery;
use App\Models\Plan;
use App\Models\PlatformUser;
use App\Models\Tenant;
use App\Support\PlatformAccess;
use Database\Seeders\PlanSeeder;
use Database\Seeders\PlatformUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlatformRbacTest extends TestCase
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

    public function test_staff_can_use_day_to_day_panel(): void
    {
        $staff = $this->staff();
        $tenant = $this->quietTenant();

        $this->actingAs($staff, 'platform')
            ->get("/{$this->path}")
            ->assertOk()
            ->assertSee('Nuevo cliente')
            ->assertDontSee('>Equipo<', false);

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
            ->withoutMiddleware(PreventRequestForgery::class)
            ->delete("/{$this->path}/clientes/{$tenant->id}")
            ->assertForbidden();

        $this->actingAs($staff, 'platform')
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post("/{$this->path}/clientes/{$tenant->id}/baja")
            ->assertForbidden();

        $this->actingAs($staff, 'platform')
            ->get("/{$this->path}/planes/{$plan->id}/editar")
            ->assertForbidden();

        $this->actingAs($staff, 'platform')
            ->withoutMiddleware(PreventRequestForgery::class)
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

    public function test_staff_cannot_open_team_pages(): void
    {
        $staff = $this->staff();

        $this->actingAs($staff, 'platform')
            ->get("/{$this->path}/equipo")
            ->assertForbidden();

        $this->actingAs($staff, 'platform')
            ->get("/{$this->path}/equipo/roles")
            ->assertForbidden();
    }

    public function test_billing_only_sees_clients_and_payments(): void
    {
        $billing = $this->billing();
        $tenant = $this->quietTenant();

        $this->actingAs($billing, 'platform')
            ->get("/{$this->path}")
            ->assertOk()
            ->assertDontSee('Nuevo cliente')
            ->assertDontSee('>Equipo<', false);

        $this->actingAs($billing, 'platform')
            ->get("/{$this->path}/clientes")
            ->assertOk();

        $this->actingAs($billing, 'platform')
            ->get("/{$this->path}/clientes/nuevo")
            ->assertForbidden();

        $this->actingAs($billing, 'platform')
            ->get("/{$this->path}/clientes/{$tenant->id}")
            ->assertOk()
            ->assertSee('Registrar pago')
            ->assertDontSee('Suspender')
            ->assertDontSee('Eliminar')
            ->assertDontSee('Copiar catálogo')
            ->assertDontSee('Copiar desde');

        $this->actingAs($billing, 'platform')
            ->get("/{$this->path}/planes")
            ->assertForbidden();

        $this->actingAs($billing, 'platform')
            ->get("/{$this->path}/equipo")
            ->assertForbidden();
    }

    public function test_admin_keeps_destructive_actions_and_team(): void
    {
        $admin = PlatformUser::first();
        $tenant = $this->quietTenant();
        $plan = Plan::first();

        $this->actingAs($admin, 'platform')
            ->get("/{$this->path}")
            ->assertOk()
            ->assertSee('Equipo');

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

        $this->actingAs($admin, 'platform')
            ->get("/{$this->path}/equipo")
            ->assertOk()
            ->assertSee('Nuevo usuario');

        $this->actingAs($admin, 'platform')
            ->get("/{$this->path}/equipo/roles")
            ->assertOk()
            ->assertSee('admin')
            ->assertSee('staff')
            ->assertSee('billing');
    }

    public function test_cannot_remove_the_last_admin(): void
    {
        $admin = PlatformUser::first();

        $this->actingAs($admin, 'platform')
            ->withoutMiddleware(PreventRequestForgery::class)
            ->put("/{$this->path}/equipo/{$admin->id}", [
                'name' => $admin->name,
                'email' => $admin->email,
                'roles' => [PlatformAccess::ROLE_STAFF],
            ])
            ->assertRedirect();

        $this->assertTrue($admin->fresh()->hasRole(PlatformAccess::ROLE_ADMIN));

        $this->actingAs($admin, 'platform')
            ->withoutMiddleware(PreventRequestForgery::class)
            ->delete("/{$this->path}/equipo/{$admin->id}")
            ->assertRedirect();

        $this->assertDatabaseHas('platform_users', ['id' => $admin->id]);
    }

    public function test_cannot_create_superadmin_or_delete_admin_role(): void
    {
        $admin = PlatformUser::first();
        $adminRole = Role::findByName(PlatformAccess::ROLE_ADMIN, PlatformAccess::GUARD);

        $this->actingAs($admin, 'platform')
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post("/{$this->path}/equipo/roles", [
                'name' => 'superadmin',
                'permissions' => ['tenants.view'],
            ])
            ->assertSessionHasErrors('name');

        $this->actingAs($admin, 'platform')
            ->withoutMiddleware(PreventRequestForgery::class)
            ->delete("/{$this->path}/equipo/roles/{$adminRole->id}")
            ->assertRedirect();

        $this->assertTrue(Role::where('name', PlatformAccess::ROLE_ADMIN)->where('guard_name', PlatformAccess::GUARD)->exists());
    }

    public function test_session_cookies_are_not_forced_secure_in_tests(): void
    {
        $this->assertFalse((bool) config('session.secure'));
    }

    private function staff(): PlatformUser
    {
        $user = PlatformUser::create([
            'name' => 'Staff',
            'email' => 'staff@arandutech.com',
            'password' => Hash::make('secret'),
            'role' => PlatformUser::ROLE_STAFF,
        ]);
        $user->assignRole(PlatformAccess::ROLE_STAFF);

        return $user;
    }

    private function billing(): PlatformUser
    {
        $user = PlatformUser::create([
            'name' => 'Cobros',
            'email' => 'billing@arandutech.com',
            'password' => Hash::make('secret'),
            'role' => PlatformAccess::ROLE_BILLING,
        ]);
        $user->assignRole(PlatformAccess::ROLE_BILLING);

        return $user;
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
