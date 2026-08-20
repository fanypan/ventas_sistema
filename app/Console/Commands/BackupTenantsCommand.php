<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupTenantsCommand extends Command
{
    protected $signature = 'tenants:backup {--path= : Directorio destino en storage/app}';

    protected $description = 'Dump de la base central y de cada tenant a storage/app/backups';

    public function handle(): int
    {
        $stamp = now()->format('Y-m-d_His');
        $relative = trim((string) ($this->option('path') ?: 'backups/'.$stamp), '/');
        $dir = storage_path('app/'.$relative);
        File::ensureDirectoryExists($dir);

        $this->dumpDatabase(config('database.connections.'.config('database.default').'.database'), $dir.'/central.sql');

        Tenant::all()->each(function (Tenant $tenant) use ($dir) {
            $name = $tenant->database()->getName();
            $this->dumpDatabase($name, $dir.'/tenant_'.$tenant->slug.'.sql');
        });

        $this->info('Backups en '.$relative);

        return self::SUCCESS;
    }

    private function dumpDatabase(?string $database, string $file): void
    {
        if (! $database) {
            return;
        }

        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (($config['driver'] ?? '') === 'sqlite') {
            $source = $config['database'] === ':memory:' ? null : $config['database'];
            if ($source && is_file($source)) {
                File::copy($source, $file);
            }

            return;
        }

        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
            escapeshellarg($config['host'] ?? '127.0.0.1'),
            escapeshellarg((string) ($config['port'] ?? 3306)),
            escapeshellarg($config['username'] ?? ''),
            escapeshellarg($config['password'] ?? ''),
            escapeshellarg($database),
            escapeshellarg($file)
        );

        $this->info('Dump '.$database);
        passthru($cmd);
    }
}
