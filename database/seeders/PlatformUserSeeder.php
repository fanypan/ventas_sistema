<?php

namespace Database\Seeders;

use App\Models\PlatformUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PlatformUserSeeder extends Seeder
{
    public static function password(): string
    {
        $password = trim((string) (
            config('saas.platform_admin_password')
            ?: getenv('PLATFORM_ADMIN_PASSWORD')
            ?: ''
        ));

        if ($password !== '') {
            return $password;
        }

        if (app()->environment('production')) {
            throw new \RuntimeException(
                'Definí PLATFORM_ADMIN_PASSWORD para sembrar el usuario staff en producción.'
            );
        }

        return 'plataforma';
    }

    public function run(): void
    {
        $this->call(PlatformPermissionSeeder::class);

        $user = PlatformUser::updateOrCreate(
            ['email' => 'plataforma@arandutech.com'],
            [
                'name' => 'AranduTech',
                'password' => Hash::make(self::password()),
                'role' => PlatformUser::ROLE_ADMIN,
            ]
        );

        $user->syncRoles([PlatformUser::ROLE_ADMIN]);
        $user->syncLegacyRoleColumn();
    }
}
