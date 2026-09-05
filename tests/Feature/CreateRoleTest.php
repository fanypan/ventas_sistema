<?php

namespace Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TenantTestCase;

class CreateRoleTest extends TenantTestCase
{
    public function test_admin_can_open_roles_page(): void
    {
        $this->actingAs($this->tenantUser)
            ->tenantGet('/admin/role')
            ->assertOk()
            ->assertSee('Nuevo rol')
            ->assertSee('Ventas')
            ->assertSee('Crear')
            ->assertDontSee('>web<', false)
            ->assertDontSee('>api<', false);
    }

    public function test_admin_can_create_role_and_assign_it(): void
    {
        $this->actingAs($this->tenantUser)
            ->from('http://demo.localhost/admin/role')
            ->tenantPost('/admin/role', [
                'name' => 'cajero',
                'guard_name' => 'api',
                'permissions' => ['create sale', 'read sale', 'read cash'],
            ])
            ->assertRedirect('http://demo.localhost/admin/role');

        $this->tenant->run(function () {
            $role = Role::findByName('cajero', 'web');
            $this->assertSame('web', $role->guard_name);
            $this->assertTrue($role->hasPermissionTo('create sale'));
            $this->assertTrue($role->hasPermissionTo('read cash'));
        });

        $this->actingAs($this->tenantUser)
            ->tenantGet('/admin/user')
            ->assertOk()
            ->assertSee('value="cajero"', false)
            ->assertSee('creá un rol');

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
        });
    }

    public function test_role_name_must_be_unique(): void
    {
        $this->actingAs($this->tenantUser)
            ->from('http://demo.localhost/admin/role')
            ->tenantPost('/admin/role', [
                'name' => 'admin',
                'permissions' => ['read sale'],
            ])
            ->assertRedirect('http://demo.localhost/admin/role')
            ->assertSessionHasErrors('name');
    }

    public function test_role_requires_at_least_one_permission(): void
    {
        $this->actingAs($this->tenantUser)
            ->from('http://demo.localhost/admin/role')
            ->tenantPost('/admin/role', [
                'name' => 'cajero',
                'permissions' => [],
            ])
            ->assertRedirect('http://demo.localhost/admin/role')
            ->assertSessionHasErrors('permissions');
    }
}
