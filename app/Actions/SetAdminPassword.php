<?php

namespace App\Actions;

use App\Exceptions\BusinessRuleException;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SetAdminPassword
{
    public function execute(User $user, string $password): void
    {
        if (! $user->must_change_password) {
            throw new BusinessRuleException('Ya definiste tu contraseña. Ingresá con tu usuario.');
        }

        DB::transaction(function () use ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'must_change_password' => false,
            ])->save();

            $tenant = tenant();

            if ($tenant instanceof Tenant && $user->email === $tenant->admin_email) {
                $tenant->update(['admin_password_set_at' => now()]);
            }
        });
    }
}
