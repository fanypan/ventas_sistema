<?php

namespace Modules\Financials\Actions;

use App\Exceptions\BusinessRuleException;
use App\Services\Billing\PlanLimitService;
use Modules\Financials\Entities\Caja;

class OpenCaja
{
    public function execute(int $userId, float $openingAmount): Caja
    {
        $limits = app(PlanLimitService::class);

        if (Caja::openForUser($userId)) {
            throw new BusinessRuleException($limits->cajaAlreadyOpenMessage());
        }

        if (! $limits->canOpenCaja()) {
            throw new BusinessRuleException($limits->cajaLimitMessage());
        }

        return Caja::create([
            'user_id' => $userId,
            'opening_amount' => $openingAmount,
            'closing_amount' => 0,
            'opened_at' => now(),
            'status' => 1,
        ]);
    }
}
