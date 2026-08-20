<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TenantCredentialsMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Tenant $tenant, public string $plainPassword)
    {
    }

    public function build()
    {
        return $this->subject('Acceso a '.config('saas.brand'))
            ->view('emails.tenant-credentials');
    }
}
