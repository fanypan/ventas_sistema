<?php

namespace App\Console\Commands;

use App\Services\Billing\SubscriptionService;
use Illuminate\Console\Command;

class TickSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:tick';

    protected $description = 'Actualiza gracia, solo lectura y suspensión de suscripciones vencidas';

    public function handle(SubscriptionService $subscriptions): int
    {
        $subscriptions->tick();
        $this->info('Suscripciones actualizadas.');

        return self::SUCCESS;
    }
}
