<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;

class BackupTenantsCommand extends Command
{
    protected $signature = 'tenants:backup {--path= : Directorio destino en storage/app}';

    protected $description = 'Dump de la base central y de cada tenant a storage/app/backups';

    public function handle(): int
    {
        $stamp = now()->format('Y-m-d_His');
        $relative = trim((string) ($this->option('path') ?: $this->backupDirectory().'/'.$stamp), '/');
        $dir = storage_path('app/'.$relative);
        File::ensureDirectoryExists($dir);

        $ext = $this->shouldCompress() ? '.sql.gz' : '.sql';

        $this->dumpDatabase(
            config('database.connections.'.config('database.default').'.database'),
            $dir.'/central'.$ext
        );

        Tenant::all()->each(function (Tenant $tenant) use ($dir, $ext) {
            $name = $tenant->database()->getName();
            $this->dumpDatabase($name, $dir.'/tenant_'.$tenant->slug.$ext);
        });

        $this->pruneOldBackups();

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
        $driver = $config['driver'] ?? '';

        if ($driver === 'sqlite') {
            $this->dumpSqlite($database, $file);

            return;
        }

        if ($driver !== 'pgsql') {
            throw new RuntimeException(
                "tenants:backup solo soporta pgsql (actual: {$driver})."
            );
        }

        $this->info('Dump '.$database);
        $this->dumpPgsql($database, $file, $config);
    }

    private function dumpSqlite(string $database, string $file): void
    {
        $source = $this->sqliteSourcePath($database);
        if (! $source) {
            return;
        }

        if ($this->shouldCompress()) {
            $this->gzipFile($source, $file);

            return;
        }

        File::copy($source, $file);
    }

    private function sqliteSourcePath(string $database): ?string
    {
        if ($database === ':memory:') {
            return null;
        }

        if (is_file($database)) {
            return $database;
        }

        $inDatabaseDir = database_path($database);
        if (is_file($inDatabaseDir)) {
            return $inDatabaseDir;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function dumpPgsql(string $database, string $file, array $config): void
    {
        $plain = $this->shouldCompress() ? $file.'.plain.tmp' : $file;

        $process = new Process([
            'pg_dump',
            '--host='.($config['host'] ?? '127.0.0.1'),
            '--port='.($config['port'] ?? 5432),
            '--username='.($config['username'] ?? ''),
            '--dbname='.$database,
            '--no-owner',
            '--format=plain',
            '--file='.$plain,
        ]);
        $process->setTimeout(300);
        $process->mustRun(null, [
            'PGPASSWORD' => (string) ($config['password'] ?? ''),
        ]);

        if (! $this->shouldCompress()) {
            return;
        }

        $this->gzipFile($plain, $file);
        File::delete($plain);
    }

    private function gzipFile(string $source, string $destination): void
    {
        $in = fopen($source, 'rb');
        if ($in === false) {
            throw new RuntimeException("No se pudo leer {$source}.");
        }

        $out = gzopen($destination, 'wb9');
        if ($out === false) {
            fclose($in);
            throw new RuntimeException("No se pudo comprimir {$destination}.");
        }

        try {
            while (! feof($in)) {
                $chunk = fread($in, 1024 * 1024);
                if ($chunk === false || $chunk === '') {
                    break;
                }
                gzwrite($out, $chunk);
            }
        } finally {
            fclose($in);
            gzclose($out);
        }
    }

    private function pruneOldBackups(): void
    {
        $keepDays = (int) config('backup.keep_days', 14);
        if ($keepDays <= 0) {
            return;
        }

        $root = storage_path('app/'.$this->backupDirectory());
        if (! is_dir($root)) {
            return;
        }

        $cutoff = now()->subDays($keepDays);

        foreach (File::directories($root) as $dir) {
            $name = basename($dir);
            if (! preg_match('/^\d{4}-\d{2}-\d{2}_\d{6}$/', $name)) {
                continue;
            }

            $stamp = \DateTimeImmutable::createFromFormat('Y-m-d_His', $name);
            if ($stamp === false) {
                continue;
            }

            if ($stamp->getTimestamp() < $cutoff->getTimestamp()) {
                File::deleteDirectory($dir);
                $this->info('Borrado dump viejo '.$name);
            }
        }
    }

    private function shouldCompress(): bool
    {
        return (bool) config('backup.compress', true);
    }

    private function backupDirectory(): string
    {
        return trim((string) config('backup.directory', 'backups'), '/');
    }
}
