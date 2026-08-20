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

        $admin = Role::findByName('admin');
        $operator = Role::findByName('operator');
        $superadmin = Role::where('name', 'superadmin')->first();

        $admin->givePermissionTo(array_merge(
            $this->crudMany($crudResources),
            [
                'void sale',
                'consume insumo',
                'read report',
                'delete user', 'update user', 'read user', 'create user',
                'read role',
                'read permission',
                'delete setting', 'update setting', 'read setting', 'create setting',
            ]
        ));

        $operator->givePermissionTo([
            'read customer', 'create customer', 'update customer',
            'read product', 'read category', 'read brand',
            'read sale', 'create sale',
            'read cash', 'create cash', 'update cash',
            'read expense', 'create expense',
            'read credit', 'create credit',
        ]);

        $superadmin?->givePermissionTo(Permission::all());

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function resourcesFromModules(): array
    {
        $resources = [];

        foreach (Module::getOrdered() as $module) {
            $jsonPath = $module->getPath() . '/module.json';
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
