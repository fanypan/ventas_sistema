<?php

namespace App\Actions\Platform;

use App\Exceptions\BusinessRuleException;
use App\Models\PlatformUser;
use App\Support\PlatformAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdatePlatformUser
{
    public function execute(PlatformUser $user, array $data, PlatformUser $actor): PlatformUser
    {
        $roles = $data['roles'];
        $keepsAdmin = in_array(PlatformAccess::ROLE_ADMIN, $roles, true);

        if ($user->isAdmin() && ! $keepsAdmin && PlatformUser::adminCount() <= 1) {
            throw new BusinessRuleException('Tiene que quedar por lo menos un admin.');
        }

        if ($user->is($actor) && ! $keepsAdmin && $actor->isAdmin() && PlatformUser::adminCount() <= 1) {
            throw new BusinessRuleException('No podés sacarte el rol admin si sos el único.');
        }

        return DB::transaction(function () use ($user, $data, $roles) {
            $payload = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            if (! empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $user->update($payload);
            $user->syncRoles($roles);
            $user->syncLegacyRoleColumn();

            return $user;
        });
    }
}
