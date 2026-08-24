<?php

namespace Tests\Feature;

use Spatie\Permission\PermissionRegistrar;
use Tests\TenantTestCase;

class TenantPermissionIsolationTest extends TenantTestCase
{
    public function test_spatie_cache_key_is_unique_per_tenant_and_resets_on_central(): void
    {
        $central = config('permission.cache.key');
        $tenantKey = $central.'.'.$this->tenant->getTenantKey();

        $this->tenant->run(function () use ($tenantKey) {
            $this->assertSame($tenantKey, PermissionRegistrar::$cacheKey);
            app(PermissionRegistrar::class)->getPermissions();
            $this->assertTrue(cache()->has($tenantKey));
        });

        $this->assertSame($central, PermissionRegistrar::$cacheKey);
        $this->assertTrue(cache()->has($tenantKey));
    }
}
