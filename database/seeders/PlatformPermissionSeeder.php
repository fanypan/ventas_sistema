<?php

namespace Database\Seeders;

use App\Models\PlatformUser;
use App\Support\PlatformAccess;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PlatformPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = PlatformAccess::GUARD;

        foreach (PlatformAccess::names() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        }

        $admin = Role::firstOrCreate(['name' => PlatformAccess::ROLE_ADMIN, 'guard_name' => $guard]);
        $staff = Role::firstOrCreate(['name' => PlatformAccess::ROLE_STAFF, 'guard_name' => $guard]);
        $billing = Role::firstOrCreate(['name' => PlatformAccess::ROLE_BILLING, 'guard_name' => $guard]);

        $admin->syncPermissions(PlatformAccess::names());
        $staff->syncPermissions(PlatformAccess::staffPermissions());
        $billing->syncPermissions(PlatformAccess::billingPermissions());

        PlatformUser::query()->each(function (PlatformUser $user) {
            if ($user->roles()->exists()) {
                return;
            }

            $role = $user->role === PlatformUser::ROLE_ADMIN
                ? PlatformAccess::ROLE_ADMIN
                : PlatformAccess::ROLE_STAFF;

            $user->syncRoles([$role]);
        });
    }
}
