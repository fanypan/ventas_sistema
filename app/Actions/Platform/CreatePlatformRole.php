<?php

namespace App\Actions\Platform;

use App\Exceptions\BusinessRuleException;
use App\Support\PlatformAccess;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class CreatePlatformRole
{
    public function execute(array $data): Role
    {
        $name = strtolower(trim($data['name']));

        if (PlatformAccess::isProtectedRole($name) || $name === 'superadmin') {
            throw new BusinessRuleException('Ese nombre de rol está reservado.');
        }

        return DB::transaction(function () use ($name, $data) {
            $role = Role::create([
                'name' => $name,
                'guard_name' => PlatformAccess::GUARD,
            ]);
            $role->syncPermissions($data['permissions'] ?? []);

            return $role;
        });
    }
}
