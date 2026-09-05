<?php

namespace App\Actions\Platform;

use App\Exceptions\BusinessRuleException;
use App\Models\PlatformUser;

class DeletePlatformUser
{
    public function execute(PlatformUser $user, PlatformUser $actor): void
    {
        if ($user->is($actor)) {
            throw new BusinessRuleException('No podés eliminarte a vos mismo.');
        }

        if ($user->isAdmin() && PlatformUser::adminCount() <= 1) {
            throw new BusinessRuleException('Tiene que quedar por lo menos un admin.');
        }

        $user->delete();
    }
}
