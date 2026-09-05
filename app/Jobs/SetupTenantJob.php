<?php

namespace App\Jobs;

use App\Actions\Platform\CloneCatalog;
use App\Actions\Platform\SendAdminInvite;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Media\TenantLogoService;
use App\Services\Tenancy\TenantProvisioningRollback;
use Database\Seeders\TenantDatabaseSeeder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Customers\Entities\Customer;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SetupTenantJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public Tenant $tenant) {}

    public function handle(): void
    {
        try {
            $this->provision();
        } catch (\Throwable $e) {
            app(TenantProvisioningRollback::class)->rollback($this->tenant);

            throw $e;
        }

        $this->applyPendingLogo();
        $this->cloneCatalogIfRequested();
    }

    public function failed(\Throwable $exception): void
    {
        app(TenantProvisioningRollback::class)->rollback($this->tenant);
    }

    private function provision(): void
    {
        $this->tenant->run(function () {
            app(TenantDatabaseSeeder::class)->run();

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $user = User::firstOrNew(['email' => $this->tenant->admin_email]);
            $user->forceFill([
                'name' => $this->tenant->admin_name ?: $this->tenant->name,
                'password' => Hash::make(Str::password(32)),
                'must_change_password' => true,
            ])->save();

            Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
            $user->syncRoles(['admin']);

            if ($this->tenant->name) {
                Setting::updateOrCreate(['key' => 'company_name'], [
                    'value' => $this->tenant->name,
                    'name' => 'Nombre de la Empresa',
                    'type' => 'text',
                    'category' => 'company',
                ]);
                Setting::updateOrCreate(['key' => 'app_name'], [
                    'value' => $this->tenant->name,
                    'name' => 'Application Short Name',
                    'type' => 'text',
                    'category' => 'information',
                ]);
            }

            if ($this->tenant->ruc) {
                Setting::updateOrCreate(['key' => 'company_nit'], [
                    'value' => $this->tenant->ruc,
                    'name' => 'NIT / RUC',
                    'type' => 'text',
                    'category' => 'company',
                ]);
            }

            Customer::firstOrCreate(
                ['nit' => '0'],
                [
                    'name' => 'Consumidor Final',
                    'user_id' => $user->id,
                    'status' => 1,
                ]
            );

            File::ensureDirectoryExists(storage_path('app/file-manager'));
        });

        $storage = storage_path();
        File::ensureDirectoryExists($storage.'/app/public');
        File::ensureDirectoryExists($storage.'/framework/cache/data');
        File::ensureDirectoryExists($storage.'/framework/sessions');
        File::ensureDirectoryExists($storage.'/framework/views');
        File::ensureDirectoryExists($storage.'/logs');

        $this->tenant->update([
            'status' => $this->tenant->status === Tenant::STATUS_PENDING ? Tenant::STATUS_ACTIVE : $this->tenant->status,
            'provisioned_at' => now(),
            'admin_password_hash' => null,
            'setup_password' => null,
            'admin_password_set_at' => null,
        ]);

        try {
            app(SendAdminInvite::class)->execute($this->tenant->fresh());
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function applyPendingLogo(): void
    {
        if (! $this->tenant->pending_logo_path) {
            return;
        }

        try {
            $this->tenant->refresh();
            app(TenantLogoService::class)->applyPending($this->tenant);
        } catch (\Throwable $e) {
            if (app()->runningUnitTests()) {
                throw $e;
            }

            report($e);
        }
    }

    private function cloneCatalogIfRequested(): void
    {
        $this->tenant->refresh();
        $sourceId = $this->tenant->catalog_source_id;
        if (! $sourceId) {
            return;
        }

        $this->tenant->catalog_source_id = null;
        $this->tenant->save();

        $source = Tenant::find($sourceId);
        if (! $source) {
            return;
        }

        try {
            app(CloneCatalog::class)->execute($source, $this->tenant->fresh());
        } catch (\Throwable $e) {
            if (app()->runningUnitTests()) {
                throw $e;
            }

            report($e);
        }
    }
}
