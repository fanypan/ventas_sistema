<?php

namespace Tests\Unit;

use App\Support\PostgreSqlTenantDatabase;
use Illuminate\Database\ConnectionInterface;
use Mockery;
use Tests\TestCase;

class PostgreSqlTenantDatabaseTest extends TestCase
{
    public function test_quotes_identifiers_for_postgres(): void
    {
        $this->assertSame('"tenant_demo"', PostgreSqlTenantDatabase::quoteIdent('tenant_demo'));
        $this->assertSame('"tenant_""x"""', PostgreSqlTenantDatabase::quoteIdent('tenant_"x"'));
    }

    public function test_drop_terminates_sessions_and_forces_drop(): void
    {
        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldReceive('select')
            ->once()
            ->with(
                'SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = ? AND pid <> pg_backend_pid()',
                ['tenant_demo']
            );
        $connection->shouldReceive('statement')
            ->once()
            ->with('DROP DATABASE IF EXISTS "tenant_demo" WITH (FORCE)');

        PostgreSqlTenantDatabase::drop('tenant_demo', $connection);
    }
}
