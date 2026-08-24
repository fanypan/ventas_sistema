<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant;
use App\Services\Tenancy\TenantProvisioningRollback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stancl\Tenancy\Jobs\MigrateDatabase;

class MigrateTenantDatabaseJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Tenant $tenant)
    {
    }

    public function handle(): void
    {
        try {
            (new MigrateDatabase($this->tenant))->handle();
        } catch (\Throwable $e) {
            app(TenantProvisioningRollback::class)->rollback($this->tenant);

            throw $e;
        }
    }
}
