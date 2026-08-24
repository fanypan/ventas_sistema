<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Support\TenantPermissionCache;
use Spatie\Permission\PermissionRegistrar;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;
use Tests\TestCase;

class TenantPermissionCacheTest extends TestCase
{
    public function test_scopes_cache_key_to_tenant_and_restores_central(): void
    {
        $central = config('permission.cache.key');
        app(PermissionRegistrar::class)->cacheKey = $central;

        $tenant = new Tenant;
        $tenant->id = 'tenant-abc';
        $this->app->instance(TenantContract::class, $tenant);

        TenantPermissionCache::scopeToCurrentTenant();
        $this->assertSame($central.'.tenant-abc', app(PermissionRegistrar::class)->cacheKey);

        TenantPermissionCache::scopeToCentral();
        $this->assertSame($central, app(PermissionRegistrar::class)->cacheKey);
    }
}
