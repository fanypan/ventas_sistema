<?php

namespace Modules\Credits\Actions;

use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;
use Modules\Credits\Entities\Abono;
use Modules\Financials\Entities\Caja;
use Modules\Purchases\Entities\Purchase;
use Modules\Sales\Entities\Sale;

class StoreAbono
{
    private const ABONABLE = [
        Sale::class,
        Purchase::class,
    ];

    public function execute(array $data, int $userId): Abono
    {
        $caja = Caja::openForUser($userId);
        if (! $caja) {
            throw new BusinessRuleException('Abrí tu caja para registrar cobros. Finanzas → Cajas.');
        }

        $modelClass = $data['abonable_type'];
        if (! in_array($modelClass, self::ABONABLE, true)) {
            throw new BusinessRuleException('No se pudo registrar el pago. Intentá de nuevo.');
        }

        try {
            return DB::transaction(function () use ($data, $userId, $caja, $modelClass) {
                $abono = Abono::create([
                    'abonable_id' => $data['abonable_id'],
                    'abonable_type' => $modelClass,
                    'amount' => $data['amount'],
                    'payment_method' => $data['payment_method'],
                    'payment_date' => now(),
                    'reference' => $data['reference'] ?? null,
                    'note' => $data['note'] ?? null,
                    'received_amount' => $data['received_amount'] ?? null,
                    'installment_number' => $data['installment_number'] ?? null,
                    'user_id' => $userId,
                    'cash_id' => $caja->id,
                ]);

                $model = $modelClass::find($data['abonable_id']);
                if (! $model) {
                    throw new BusinessRuleException('No se pudo registrar el pago. Intentá de nuevo.');
                }

                if ($model instanceof Sale && $model->installments_count > 0) {
                    $this->applyToInstallments($model, (float) $data['amount']);
                }

                if ($model->pending_balance() <= 0) {
                    $model->status = 1;
                    $model->save();
                }

                return $abono;
            });
        } catch (BusinessRuleException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            throw new BusinessRuleException('No se pudo registrar el pago. Intentá de nuevo.');
        }
    }

    private function applyToInstallments(Sale $sale, float $remainingToApply): void
    {
        $installments = $sale->installments()
            ->pending()
            ->orderBy('installment_number')
            ->get();

        foreach ($installments as $inst) {
            if ($remainingToApply <= 0) {
                break;
            }

            $pendingOnThis = (float) $inst->amount - (float) $inst->paid_amount;
            if ($remainingToApply >= $pendingOnThis) {
                $inst->paid_amount = $inst->amount;
                $inst->status = 1;
                $inst->paid_at = now();
                $remainingToApply -= $pendingOnThis;
            } else {
                $inst->paid_amount = (float) $inst->paid_amount + $remainingToApply;
                $remainingToApply = 0;
            }
            $inst->save();
        }
    }
}
