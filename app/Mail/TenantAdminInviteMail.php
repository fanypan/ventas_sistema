<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TenantAdminInviteMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Tenant $tenant, public string $setupUrl) {}

    public function build()
    {
        return $this->subject('Definí tu contraseña — '.config('saas.brand'))
            ->view('emails.tenant-admin-invite');
    }
}
