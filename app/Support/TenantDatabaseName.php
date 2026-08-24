<?php

namespace App\Support;

use App\Models\Tenant;
use InvalidArgumentException;

final class TenantDatabaseName
{
    private const PREFIX = 'tenant_';

    /** Límite de identificadores de PostgreSQL (NAMEDATALEN - 1). */
    private const MAX_LENGTH = 63;

    /** Letras minúsculas y dígitos: sin `-` ni `_` (Postgres no admite guion sin comillas). */
    public const SLUG_PATTERN = '/^[a-z0-9]+$/';

    public const SLUG_MAX_LENGTH = 56;

    public static function slugIsValid(string $slug): bool
    {
        return (bool) preg_match(self::SLUG_PATTERN, $slug) && strlen($slug) <= self::SLUG_MAX_LENGTH;
    }

    public static function for(Tenant $tenant): string
    {
        $slug = $tenant->slug;

        if (! $slug) {
            return config('tenancy.database.prefix').$tenant->getTenantKey().config('tenancy.database.suffix');
        }

        if (! self::slugIsValid($slug)) {
            throw new InvalidArgumentException(
                'El slug solo puede tener letras minúsculas y números, sin guiones (máx. '
                .self::SLUG_MAX_LENGTH.' caracteres).'
            );
        }

        $name = self::PREFIX.$slug;

        if (strlen($name) > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                'El slug supera el límite para el nombre de base de datos (máx. '
                .(self::MAX_LENGTH - strlen(self::PREFIX)).' caracteres).'
            );
        }

        return $name;
    }
}
