<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            PlanSeeder::class,
            PlatformPermissionSeeder::class,
            PlatformUserSeeder::class,
        ]);
    }
}
