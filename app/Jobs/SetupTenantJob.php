<?php

namespace App\Jobs;

use App\Mail\TenantCredentialsMail;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\TenantDatabaseSeeder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Modules\Customers\Entities\Customer;
use Spatie\Permission\PermissionRegistrar;

class SetupTenantJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public Tenant $tenant, public string $plainPassword)
    {
    }

    public function handle(): void
    {
        $this->tenant->run(function () {
            Artisan::call('db:seed', [
                '--class' => TenantDatabaseSeeder::class,
                '--force' => true,
            ]);

            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            $user = User::updateOrCreate(
                ['email' => $this->tenant->admin_email],
                [
                    'name' => $this->tenant->admin_name ?: $this->tenant->name,
                    'password' => $this->tenant->admin_password_hash,
                ]
            );
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
        ]);

        if ($this->tenant->admin_email) {
            Mail::to($this->tenant->admin_email)->send(new TenantCredentialsMail($this->tenant, $this->plainPassword));
        }
    }
}
