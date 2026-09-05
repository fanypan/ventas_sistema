<?php

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class CreateRole
{
    public function execute(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => 'web',
            ]);
            $role->givePermissionTo($data['permissions']);

            return $role;
        });
    }
}
