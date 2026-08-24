<?php

namespace App\Support;

use Spatie\Permission\PermissionRegistrar;

final class TenantPermissionCache
{
    public static function scopeToCurrentTenant(): void
    {
        $id = tenant()?->getTenantKey();

        if ($id === null || $id === '') {
            return;
        }

        self::useKey(config('permission.cache.key').'.'.$id);
    }

    public static function scopeToCentral(): void
    {
        self::useKey((string) config('permission.cache.key'));
    }

    private static function useKey(string $key): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->cacheKey = $key;
        $registrar->clearPermissionsCollection();
    }
}
