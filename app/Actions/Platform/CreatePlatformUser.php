<?php

namespace App\Actions\Platform;

use App\Models\PlatformUser;
use App\Support\PlatformAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreatePlatformUser
{
    public function execute(array $data): PlatformUser
    {
        return DB::transaction(function () use ($data) {
            $roles = $data['roles'];
            $legacy = in_array(PlatformAccess::ROLE_ADMIN, $roles, true)
                ? PlatformAccess::ROLE_ADMIN
                : ($roles[0] ?? PlatformAccess::ROLE_STAFF);

            $user = PlatformUser::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $legacy,
            ]);

            $user->syncRoles($roles);
            $user->syncLegacyRoleColumn();

            return $user;
        });
    }
}
