<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Support\PostgreSqlTenantDatabase;
use App\Support\TenantDatabaseName;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupOrphanTenantDatabasesCommand extends Command
{
    protected $signature = 'tenants:cleanup-orphans {--dry-run : Solo listar bases huérfanas, sin borrar}';

    protected $description = 'Elimina bases tenant_* que no tienen cliente en la plataforma';

    public function handle(): int
    {
        $driver = config('database.connections.'.config('database.default').'.driver');

        if ($driver === 'sqlite') {
            $this->warn('SQLite en tests: usá glob en database/ para limpiar archivos tenant_* huérfanos.');

            return self::SUCCESS;
        }

        if ($driver !== 'pgsql') {
            $this->error("tenants:cleanup-orphans solo soporta pgsql (actual: {$driver}).");

            return self::FAILURE;
        }

        $known = Tenant::all()
            ->mapWithKeys(fn (Tenant $tenant) => [TenantDatabaseName::for($tenant) => $tenant->slug])
            ->all();

        $orphans = collect($this->listTenantDatabases())
            ->reject(fn (string $name) => array_key_exists($name, $known));

        if ($orphans->isEmpty()) {
            $this->info('No hay bases huérfanas.');

            return self::SUCCESS;
        }

        foreach ($orphans as $database) {
            if ($this->option('dry-run')) {
                $this->line("Huérfana: {$database}");

                continue;
            }

            $this->dropTenantDatabase($database);
            $this->info("Eliminada: {$database}");
        }

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function listTenantDatabases(): array
    {
        $rows = DB::select(
            "SELECT datname AS name FROM pg_database WHERE datistemplate = false AND datname LIKE 'tenant\_%' ESCAPE '\\'"
        );

        return array_map(static fn ($row) => $row->name, $rows);
    }

    private function dropTenantDatabase(string $database): void
    {
        PostgreSqlTenantDatabase::drop($database);
    }
}
