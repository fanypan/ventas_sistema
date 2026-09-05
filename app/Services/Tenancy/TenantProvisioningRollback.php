<?php

namespace App\Services\Tenancy;

use App\Models\Tenant;
use App\Services\Media\TenantObjectStorage;
use App\Support\TenantSetupPassword;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\DatabaseManager;

class TenantProvisioningRollback
{
    public function rollback(TenantWithDatabase|string|null $tenant): void
    {
        if (is_string($tenant)) {
            $tenant = Tenant::find($tenant);
        }

        if (! $tenant instanceof Tenant) {
            return;
        }

        if ($tenant->provisioned_at !== null) {
            return;
        }

        $tenantId = $tenant->getTenantKey();
        $pendingLogo = $tenant->pending_logo_path;
        TenantSetupPassword::forget((string) $tenantId);

        $this->dropDatabase($tenant);

        try {
            Tenant::withoutEvents(function () use ($tenantId) {
                Tenant::find($tenantId)?->delete();
            });
        } catch (\Throwable $e) {
            Log::error('Tenant provisioning rollback: no se pudo borrar el registro central', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
        } finally {
            if (tenancy()->initialized) {
                tenancy()->end();
            } else {
                app(DatabaseManager::class)->reconnectToCentral();
            }

            $this->deleteFilesystem((string) $tenantId);
            $this->deletePendingLogo($pendingLogo);
        }
    }

    public function deleteFilesystem(string $tenantId): void
    {
        $dir = base_path('storage/tenant'.$tenantId);

        if (is_dir($dir)) {
            try {
                File::deleteDirectory($dir);
            } catch (\Throwable $e) {
                Log::error('Tenant provisioning rollback: no se pudo borrar el storage del tenant', [
                    'tenant_id' => $tenantId,
                    'path' => $dir,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        app(TenantObjectStorage::class)->deleteTenant($tenantId);
    }

    public function deletePendingLogo(mixed $relative): void
    {
        if (! is_string($relative) || ! preg_match('/^pending-logos\/[a-z0-9-]+\.png$/', $relative)) {
            return;
        }

        File::delete(base_path('storage/app/'.$relative));
    }

    public function dropDatabase(TenantWithDatabase $tenant): void
    {
        try {
            $manager = $tenant->database()->manager();
            $name = $tenant->database()->getName();

            if ($manager->databaseExists($name)) {
                $manager->deleteDatabase($tenant);
            }
        } catch (\Throwable $e) {
            Log::error('Tenant provisioning rollback: no se pudo eliminar la base del tenant', [
                'tenant_id' => $tenant->getTenantKey(),
                'database' => $tenant->database()->getName(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
