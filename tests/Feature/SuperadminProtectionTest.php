<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TenantTestCase;

class SuperadminProtectionTest extends TenantTestCase
{
    public function test_admin_cannot_delete_or_demote_superadmin(): void
    {
        $superId = $this->tenant->run(function () {
            $user = User::create([
                'name' => 'Super',
                'email' => 'super@demo.test',
                'password' => Hash::make('password'),
            ]);
            $user->assignRole('superadmin');

            return $user->id;
        });

        $this->actingAs($this->tenantUser)
            ->withoutMiddleware(\App\Http\Middleware\PreventRequestForgery::class)
            ->delete('http://demo.localhost/admin/user', ['id' => $superId])
            ->assertForbidden();

        $this->actingAs($this->tenantUser)
            ->withoutMiddleware(\App\Http\Middleware\PreventRequestForgery::class)
            ->put('http://demo.localhost/admin/user', [
                'id' => $superId,
                'name' => 'Hackeado',
                'email' => 'super@demo.test',
                'old_email' => 'super@demo.test',
                'role' => 'operator',
            ])
            ->assertForbidden();

        $this->tenant->run(function () use ($superId) {
            $user = User::findOrFail($superId);
            $this->assertTrue($user->hasRole('superadmin'));
            $this->assertSame('Super', $user->name);
        });
    }

    public function test_admin_cannot_mutate_superadmin_role(): void
    {
        $roleId = $this->tenant->run(function () {
            return Role::findByName('superadmin', 'web')->id;
        });

        $this->actingAs($this->tenantUser)
            ->withoutMiddleware(\App\Http\Middleware\PreventRequestForgery::class)
            ->delete('http://demo.localhost/admin/role', ['id' => $roleId])
            ->assertForbidden();

        $this->actingAs($this->tenantUser)
            ->withoutMiddleware(\App\Http\Middleware\PreventRequestForgery::class)
            ->put('http://demo.localhost/admin/role', [
                'id' => $roleId,
                'name' => 'no-super',
                'guard_name' => 'web',
                'permissions' => ['read sale'],
            ])
            ->assertForbidden();

        $this->actingAs($this->tenantUser)
            ->withoutMiddleware(\App\Http\Middleware\PreventRequestForgery::class)
            ->post('http://demo.localhost/admin/role', [
                'name' => 'superadmin',
                'guard_name' => 'web',
                'permissions' => ['read sale'],
            ])
            ->assertForbidden();

        $this->tenant->run(function () {
            $this->assertTrue(Role::where('name', 'superadmin')->exists());
        });
    }
}
