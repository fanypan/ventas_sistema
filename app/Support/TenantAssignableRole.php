<?php

namespace App\Support;

final class TenantAssignableRole
{
    public const PROTECTED = 'superadmin';

    public static function isProtected(?string $name): bool
    {
        return strtolower(trim((string) $name)) === self::PROTECTED;
    }
}
