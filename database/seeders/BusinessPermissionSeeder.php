<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Nwidart\Modules\Facades\Module;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class BusinessPermissionSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $crudResources = $this->resourcesFromModules();
        $crudResources = array_unique(array_merge($crudResources, [
            'product', 'category', 'brand',
            'sale', 'customer',
            'purchase', 'supplier',
            'credit',
            'cash', 'expense', 'insumo',
            'stock',
        ]));

        foreach ($crudResources as $resource) {
            foreach (['delete', 'update', 'read', 'create'] as $action) {
                Permission::firstOrCreate(['name' => "{$action} {$resource}", 'guard_name' => 'web']);
            }
        }

        foreach (['void sale', 'consume insumo', 'read report'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $adminPermissions = array_merge(
            $this->crudMany($crudResources),
            [
                'filemanager',
                'read module',
                'void sale',
                'consume insumo',
                'read report',
                'delete user', 'update user', 'read user', 'create user',
                'delete role', 'update role', 'read role', 'create role',
                'delete permission', 'update permission', 'read permission', 'create permission',
                'delete setting', 'update setting', 'read setting', 'create setting',
            ]
        );

        foreach ($adminPermissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $operator = Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);
        $superadmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);

        $admin->syncPermissions($adminPermissions);

        $operatorPermissions = [
            'read customer', 'create customer', 'update customer',
            'read product', 'read category', 'read brand',
            'read sale', 'create sale',
            'read cash', 'create cash', 'update cash',
            'read expense', 'create expense',
            'read credit', 'create credit',
        ];

        foreach ($operatorPermissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $operator->syncPermissions($operatorPermissions);

        $superadmin->syncPermissions(Permission::where('guard_name', 'web')->pluck('name')->all());

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function resourcesFromModules(): array
    {
        $resources = [];

        foreach (Module::getOrdered() as $module) {
            $jsonPath = $module->getPath().'/module.json';
            if (! is_file($jsonPath)) {
                continue;
            }

            $json = json_decode(file_get_contents($jsonPath));
            foreach ($json->permissions ?? [] as $permission) {
                $resources[] = $permission;
            }
        }

        return $resources;
    }

    private function crudMany(array $resources): array
    {
        $names = [];
        foreach ($resources as $resource) {
            foreach (['delete', 'update', 'read', 'create'] as $action) {
                $names[] = "{$action} {$resource}";
            }
        }

        return $names;
    }
}
