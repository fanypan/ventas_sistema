<?php

namespace App\Actions\Platform;

use App\Exceptions\BusinessRuleException;
use App\Support\PlatformAccess;
use Spatie\Permission\Models\Role;

class DeletePlatformRole
{
    public function execute(Role $role): void
    {
        if ($role->guard_name !== PlatformAccess::GUARD) {
            throw new BusinessRuleException('Ese rol no es de la plataforma.');
        }

        if (PlatformAccess::isProtectedRole($role->name)) {
            throw new BusinessRuleException('El rol admin no se puede eliminar.');
        }

        if ($role->users()->exists()) {
            throw new BusinessRuleException('Sacá el rol de los usuarios antes de eliminarlo.');
        }

        $role->delete();
    }
}
