<?php

namespace App\Tenancy;

use App\Support\PostgreSqlTenantDatabase;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\TenantDatabaseManagers\PostgreSQLDatabaseManager as BasePostgreSQLDatabaseManager;

class PostgreSQLDatabaseManager extends BasePostgreSQLDatabaseManager
{
    public function deleteDatabase(TenantWithDatabase $tenant): bool
    {
        if (function_exists('tenancy') && tenancy()->initialized) {
            tenancy()->end();
        }

        if (config('database.connections.tenant')) {
            try {
                DB::purge('tenant');
            } catch (\Throwable) {
            }
        }

        PostgreSqlTenantDatabase::drop($tenant->database()->getName());

        return true;
    }
}
