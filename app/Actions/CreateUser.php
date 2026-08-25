<?php

namespace App\Actions;

use App\Exceptions\BusinessRuleException;
use App\Models\User;
use App\Services\Billing\PlanLimitService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateUser
{
    public function execute(array $data): User
    {
        if (! app(PlanLimitService::class)->canCreateUser()) {
            throw new BusinessRuleException(app(PlanLimitService::class)->userLimitMessage());
        }

        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            $user->assignRole($data['role']);

            return $user;
        });
    }
}
