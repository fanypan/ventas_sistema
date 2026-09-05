<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MigrateTenantMediaCommand extends Command
{
    protected $signature = 'tenants:media-migrate {--disk=minio : Disco destino para fotos públicas} {--dry-run : Solo listar}';

    protected $description = 'Copia fotos y archivos locales de cada tenant al bucket MinIO';

    public function handle(): int
    {
        $disk = (string) $this->option('disk');
        $dry = (bool) $this->option('dry-run');
        $privateDisk = (string) config('media.private_disk');

        Tenant::query()->each(function (Tenant $tenant) use ($disk, $privateDisk, $dry) {
            $this->info('Tenant '.$tenant->slug);

            $tenant->run(function () use ($disk, $privateDisk, $dry, $tenant) {
                $this->copyDirectory(
                    storage_path('app/public/products'),
                    $disk,
                    'products',
                    $dry,
                    $tenant->slug,
                );
                $this->copyDirectory(
                    storage_path('app/file-manager'),
                    $privateDisk,
                    '',
                    $dry,
                    $tenant->slug,
                );
            });
        });

        return self::SUCCESS;
    }

    private function copyDirectory(string $from, string $disk, string $prefix, bool $dry, string $slug): void
    {
        if (! is_dir($from)) {
            return;
        }

        foreach (File::allFiles($from) as $file) {
            $relative = ltrim($prefix.'/'.$file->getRelativePathname(), '/');
            $this->line("  {$slug}: {$relative}");

            if ($dry) {
                continue;
            }

            Storage::disk($disk)->put($relative, File::get($file->getPathname()));
        }
    }
}
