<?php

namespace App\Actions\Platform;

use App\Exceptions\BusinessRuleException;
use App\Support\PlatformAccess;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UpdatePlatformRole
{
    public function execute(Role $role, array $data): Role
    {
        if ($role->guard_name !== PlatformAccess::GUARD) {
            throw new BusinessRuleException('Ese rol no es de la plataforma.');
        }

        $name = strtolower(trim($data['name']));

        if (PlatformAccess::isProtectedRole($role->name)) {
            $role->syncPermissions(PlatformAccess::names());

            return $role;
        }

        if (PlatformAccess::isProtectedRole($name) || $name === 'superadmin') {
            throw new BusinessRuleException('Ese nombre de rol está reservado.');
        }

        return DB::transaction(function () use ($role, $name, $data) {
            $role->update(['name' => $name]);
            $role->syncPermissions($data['permissions'] ?? []);

            return $role;
        });
    }
}
