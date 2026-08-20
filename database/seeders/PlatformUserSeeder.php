<?php

namespace Database\Seeders;

use App\Models\PlatformUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PlatformUserSeeder extends Seeder
{
    public function run(): void
    {
        PlatformUser::updateOrCreate(
            ['email' => 'plataforma@arandutech.com'],
            [
                'name' => 'AranduTech',
                'password' => Hash::make('plataforma'),
                'role' => 'admin',
            ]
        );
    }
}
