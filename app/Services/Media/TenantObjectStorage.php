<?php

namespace App\Services\Media;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TenantObjectStorage
{
    public function prefix(string $tenantId): string
    {
        return config('tenancy.filesystem.suffix_base', 'tenant').$tenantId;
    }

    public function deleteTenant(string $tenantId): void
    {
        $prefix = $this->prefix($tenantId);

        foreach (config('media.tenant_object_disks', []) as $disk) {
            $this->deleteDirectory($disk, $prefix);
        }

        $private = (string) config('media.private_disk');
        if ($private !== '' && ! in_array($private, config('media.tenant_object_disks', []), true)) {
            $this->deleteDirectory($private, $prefix);
        }

        $this->deleteDirectory((string) config('media.payment_disk'), 'payment-receipts/'.$tenantId);
    }

    private function deleteDirectory(string $disk, string $directory): void
    {
        if ($disk === '' || (config("filesystems.disks.{$disk}.driver") ?? '') !== 's3') {
            return;
        }

        try {
            Storage::disk($disk)->deleteDirectory($directory);
        } catch (\Throwable $e) {
            Log::warning('No se pudo borrar el prefijo de MinIO del tenant', [
                'disk' => $disk,
                'directory' => $directory,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
