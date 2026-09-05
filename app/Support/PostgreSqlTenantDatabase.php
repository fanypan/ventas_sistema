<?php

namespace App\Support;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

final class PostgreSqlTenantDatabase
{
    public static function quoteIdent(string $name): string
    {
        return '"'.str_replace('"', '""', $name).'"';
    }

    public static function drop(string $database, ?ConnectionInterface $connection = null): void
    {
        $connection ??= DB::connection(config('tenancy.database.central_connection'));
        $quoted = self::quoteIdent($database);

        try {
            $connection->select(
                'SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = ? AND pid <> pg_backend_pid()',
                [$database]
            );
        } catch (\Throwable) {
        }

        $connection->statement("DROP DATABASE IF EXISTS {$quoted} WITH (FORCE)");
    }
}
