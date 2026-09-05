<?php

namespace App\Actions\Platform;

use App\Exceptions\BusinessRuleException;
use App\Mail\TenantAdminInviteMail;
use App\Models\Tenant;
use App\Support\AdminInvite;
use Illuminate\Support\Facades\Mail;

class SendAdminInvite
{
    public function execute(Tenant $tenant): void
    {
        if ($tenant->provisioned_at === null) {
            throw new BusinessRuleException('El cliente todavía se está aprovisionando.');
        }

        if ($tenant->admin_password_set_at !== null) {
            throw new BusinessRuleException('El admin ya definió su contraseña.');
        }

        if (! $tenant->admin_email) {
            throw new BusinessRuleException('El cliente no tiene correo de admin.');
        }

        Mail::to($tenant->admin_email)->send(new TenantAdminInviteMail(
            $tenant,
            AdminInvite::url($tenant)
        ));
    }
}
