<?php

namespace App\Actions\Platform;

use App\Models\Tenant;
use App\Services\Tenancy\TenantProvisioningRollback;
use Illuminate\Support\Facades\DB;

class DeleteTenant
{
    public function __construct(private TenantProvisioningRollback $rollback) {}

    public function execute(Tenant $tenant): void
    {
        $tenantId = (string) $tenant->getTenantKey();
        $pendingLogo = $tenant->pending_logo_path;

        if (tenancy()->initialized) {
            tenancy()->end();
        }

        if (config('database.connections.tenant')) {
            try {
                DB::purge('tenant');
            } catch (\Throwable) {
            }
        }

        $manager = $tenant->database()->manager();
        $name = $tenant->database()->getName();

        if ($manager->databaseExists($name)) {
            $manager->deleteDatabase($tenant);
        }

        Tenant::withoutEvents(fn () => $tenant->delete());

        $this->rollback->deleteFilesystem($tenantId);
        $this->rollback->deletePendingLogo($pendingLogo);
    }
}
