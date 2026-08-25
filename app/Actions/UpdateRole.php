<?php

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UpdateRole
{
    public function execute(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data) {
            $role->update([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'],
            ]);
            $role->syncPermissions($data['permissions']);

            return $role;
        });
    }
}
