<?php

namespace App\Mail;

use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionReminderMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Tenant $tenant, public Subscription $subscription) {}

    public function build()
    {
        return $this->subject('Tu plan vence pronto — '.config('saas.brand'))
            ->view('emails.subscription-reminder');
    }
}
