<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Support\TenantDatabaseName;
use InvalidArgumentException;
use Tests\TestCase;

class TenantDatabaseNameTest extends TestCase
{
    public function test_uses_slug_with_tenant_prefix(): void
    {
        $tenant = new Tenant(['slug' => 'minegocio']);

        $this->assertSame('tenant_minegocio', TenantDatabaseName::for($tenant));
    }

    public function test_accepts_slug_at_postgres_limit(): void
    {
        $tenant = new Tenant(['slug' => str_repeat('a', 56)]);

        $this->assertSame('tenant_'.str_repeat('a', 56), TenantDatabaseName::for($tenant));
    }

    public function test_rejects_slug_with_hyphen_or_underscore(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TenantDatabaseName::for(new Tenant(['slug' => 'mi-negocio']));
    }

    public function test_rejects_slug_with_underscore(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TenantDatabaseName::for(new Tenant(['slug' => 'mi_negocio']));
    }

    public function test_rejects_slug_that_exceeds_postgres_limit(): void
    {
        $tenant = new Tenant(['slug' => str_repeat('a', 57)]);

        $this->expectException(InvalidArgumentException::class);

        TenantDatabaseName::for($tenant);
    }

    public function test_falls_back_to_tenant_key_without_slug(): void
    {
        $tenant = new Tenant();
        $tenant->setAttribute('id', 'abc-123');

        $this->assertSame('tenantabc-123', TenantDatabaseName::for($tenant));
    }
}
