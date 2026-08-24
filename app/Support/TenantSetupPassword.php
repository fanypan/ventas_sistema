<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Stancl\Tenancy\Contracts\UniqueIdentifierGenerator;

final class TenantSetupPassword
{
    public const PENDING = 'tenant.pending_setup_password';

    private const TTL_SECONDS = 3600;

    public static function cacheKey(string $tenantId): string
    {
        return 'tenant-setup-password:'.$tenantId;
    }

    public static function store(string $tenantId, string $plain): void
    {
        Cache::put(
            self::cacheKey($tenantId),
            Crypt::encryptString($plain),
            now()->addSeconds(self::TTL_SECONDS)
        );
    }

    public static function pull(string $tenantId): ?string
    {
        $payload = Cache::pull(self::cacheKey($tenantId));

        if (! is_string($payload) || $payload === '') {
            return null;
        }

        try {
            return Crypt::decryptString($payload);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function forget(string $tenantId): void
    {
        Cache::forget(self::cacheKey($tenantId));
    }

    /**
     * Saca la clave de alta del modelo antes del INSERT para que no quede en `data`.
     */
    public static function captureFromCreating(Tenant $tenant): void
    {
        self::ensureTenantKey($tenant);

        $plain = is_string($tenant->setup_password) && $tenant->setup_password !== ''
            ? $tenant->setup_password
            : (app()->bound(self::PENDING) ? app(self::PENDING) : null);

        if (! is_string($plain) || $plain === '') {
            return;
        }

        $id = $tenant->getTenantKey();

        if ($id === null || $id === '') {
            return;
        }

        self::store((string) $id, $plain);
        $tenant->setup_password = null;
    }

    private static function ensureTenantKey(Tenant $tenant): void
    {
        if ($tenant->getKey()) {
            return;
        }

        if ($tenant->shouldGenerateId()) {
            $tenant->setAttribute(
                $tenant->getKeyName(),
                app(UniqueIdentifierGenerator::class)->generate($tenant)
            );
        }
    }
}
