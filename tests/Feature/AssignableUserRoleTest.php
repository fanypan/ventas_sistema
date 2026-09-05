<?php

namespace Tests\Feature;

use App\Http\Middleware\PreventRequestForgery;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TenantTestCase;

class AssignableUserRoleTest extends TenantTestCase
{
    public function test_user_form_lists_custom_roles_but_not_superadmin(): void
    {
        $this->seedCustomRole();

        $this->actingAs($this->tenantUser)
            ->tenantGet('/admin/user')
            ->assertOk()
            ->assertSee('value="cajero"', false)
            ->assertSee('value="admin"', false)
            ->assertSee('value="operator"', false)
            ->assertDontSee('value="superadmin"', false);
    }

    public function test_admin_can_create_user_with_custom_role(): void
    {
        $this->seedCustomRole();

        $this->actingAs($this->tenantUser)
            ->from('http://demo.localhost/admin/user')
            ->tenantPost('/admin/user', [
                'name' => 'Caja 1',
                'email' => 'cajero@demo.test',
                'password' => '12345678',
                'role' => 'cajero',
            ])
            ->assertRedirect('http://demo.localhost/admin/user');

        $this->tenant->run(function () {
            $user = User::where('email', 'cajero@demo.test')->firstOrFail();
            $this->assertTrue($user->hasRole('cajero'));
            $this->assertFalse($user->hasRole('admin'));
        });
    }

    public function test_admin_can_update_user_to_custom_role(): void
    {
        $this->seedCustomRole();

        $userId = $this->tenant->run(function () {
            $user = User::create([
                'name' => 'Operador Demo',
                'email' => 'op@demo.test',
                'password' => Hash::make('password'),
            ]);
            $user->assignRole('operator');

            return $user->id;
        });

        $this->actingAs($this->tenantUser)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->from('http://demo.localhost/admin/user')
            ->put('http://demo.localhost/admin/user', [
                'id' => $userId,
                'name' => 'Operador Demo',
                'email' => 'op@demo.test',
                'role' => 'cajero',
            ])
            ->assertRedirect('http://demo.localhost/admin/user');

        $this->tenant->run(function () use ($userId) {
            $user = User::findOrFail($userId);
            $this->assertTrue($user->hasRole('cajero'));
            $this->assertFalse($user->hasRole('operator'));
        });
    }

    public function test_admin_cannot_assign_superadmin_role(): void
    {
        $this->actingAs($this->tenantUser)
            ->from('http://demo.localhost/admin/user')
            ->tenantPost('/admin/user', [
                'name' => 'Intruso',
                'email' => 'intruso@demo.test',
                'password' => '12345678',
                'role' => 'superadmin',
            ])
            ->assertRedirect('http://demo.localhost/admin/user')
            ->assertSessionHasErrors('role');

        $this->tenant->run(function () {
            $this->assertDatabaseMissing('users', ['email' => 'intruso@demo.test']);
        });
    }

    public function test_admin_cannot_assign_unknown_role(): void
    {
        $this->actingAs($this->tenantUser)
            ->from('http://demo.localhost/admin/user')
            ->tenantPost('/admin/user', [
                'name' => 'Intruso',
                'email' => 'intruso@demo.test',
                'password' => '12345678',
                'role' => 'no-existe',
            ])
            ->assertRedirect('http://demo.localhost/admin/user')
            ->assertSessionHasErrors('role');
    }

    private function seedCustomRole(): void
    {
        $this->tenant->run(function () {
            $role = Role::firstOrCreate([
                'name' => 'cajero',
                'guard_name' => 'web',
            ]);
            $role->syncPermissions(
                Permission::where('guard_name', 'web')
                    ->whereIn('name', ['create sale', 'read sale', 'read cash'])
                    ->pluck('name')
                    ->all()
            );
        });
    }
}
