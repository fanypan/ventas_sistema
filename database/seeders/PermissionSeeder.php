<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::firstOrCreate(['name' => 'filemanager']);
        Permission::firstOrCreate(['name' => 'read module']);

        Permission::firstOrCreate(['name' => 'delete setting']);
        Permission::firstOrCreate(['name' => 'update setting']);
        Permission::firstOrCreate(['name' => 'read setting']);
        Permission::firstOrCreate(['name' => 'create setting']);

        Permission::firstOrCreate(['name' => 'delete user']);
        Permission::firstOrCreate(['name' => 'update user']);
        Permission::firstOrCreate(['name' => 'read user']);
        Permission::firstOrCreate(['name' => 'create user']);

        Permission::firstOrCreate(['name' => 'delete role']);
        Permission::firstOrCreate(['name' => 'update role']);
        Permission::firstOrCreate(['name' => 'read role']);
        Permission::firstOrCreate(['name' => 'create role']);

        Permission::firstOrCreate(['name' => 'delete permission']);
        Permission::firstOrCreate(['name' => 'update permission']);
        Permission::firstOrCreate(['name' => 'read permission']);
        Permission::firstOrCreate(['name' => 'create permission']);
    }
}
