<?php

namespace Modules\Sales\Actions;

use App\Exceptions\BusinessRuleException;
use App\Services\Billing\PlanLimitService;
use App\Services\Sifen\SifenIssuer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Financials\Entities\Caja;
use Modules\Sales\Entities\Sale;
use Modules\Sales\Entities\SaleDetail;
use Modules\Sales\Entities\SaleInstallment;
use Modules\Sales\Entities\TemporaryDetail;

class ProcessSale
{
    public function execute(string $token, array $data, int $userId): array
    {
        $details = TemporaryDetail::where('user_token', $token)->get();

        if ($details->isEmpty()) {
            throw new BusinessRuleException('El carrito está vacío');
        }

        $caja = Caja::openForUser($userId);
        if (! $caja) {
            throw new BusinessRuleException('Abrí tu caja para vender. Finanzas → Cajas.');
        }

        $paymentType = $data['payment_type'] ?? 'efectivo';
        if ($paymentType === 'credito' && ! app(PlanLimitService::class)->hasFeature('credits')) {
            throw new BusinessRuleException(app(PlanLimitService::class)->featureDeniedMessage('credits'));
        }

        try {
            [$sale, $change] = DB::transaction(function () use ($details, $caja, $data, $token, $userId, $paymentType) {
                $paymentWith = parse_currency($data['payment_with'] ?? 0);
                $discountPercent = parse_currency($data['discount'] ?? 0);

                $subtotal = $details->sum(function ($detail) {
                    return $detail->quantity * $detail->price;
                });

                $discountAmount = round($subtotal * $discountPercent / 100);
                $total = $subtotal - $discountAmount;

                $status = 1;
                if ($paymentType === 'credito') {
                    $status = 2;
                    $paymentWith = 0;
                }

                if ($paymentType !== 'efectivo' && $paymentType !== 'credito') {
                    $paymentWith = $total;
                }

                if ($paymentType === 'efectivo' && $paymentWith < $total) {
                    throw new BusinessRuleException('El monto pagado no cubre el total');
                }

                $interestType = $data['interest_type'] ?? 'amount';
                $interestVal = parse_currency($data['interest_value'] ?? 0);

                $interestAmount = $interestType === 'percent'
                    ? round($total * $interestVal / 100)
                    : $interestVal;

                $installmentsCount = (int) ($data['installments'] ?? 1);
                $jsonChange = $paymentType === 'efectivo' ? max(0, $paymentWith - $total) : 0;

                $sale = Sale::create([
                    'user_id' => $userId,
                    'customer_id' => $data['customer_id'] ?? 1,
                    'total' => $total + $interestAmount,
                    'discount' => $discountAmount,
                    'interest_amount' => $interestAmount,
                    'installments_count' => $installmentsCount,
                    'payment_type' => $paymentType,
                    'payment_with' => $paymentWith,
                    'change' => $paymentType === 'efectivo' ? max(0, $paymentWith - ($total + $interestAmount)) : 0,
                    'reference_number' => $data['reference_number'] ?? null,
                    'payment_note' => $data['payment_note'] ?? null,
                    'status' => $status,
                    'cash_id' => $caja->id,
                ]);

                if ($paymentType === 'credito') {
                    $this->createInstallments($sale, $data, $total + $interestAmount, $installmentsCount);
                }

                foreach ($details as $detail) {
                    SaleDetail::create([
                        'sale_id' => $sale->id,
                        'product_id' => $detail->product_id,
                        'quantity' => $detail->quantity,
                        'price' => $detail->price,
                        'cost' => $detail->cost,
                        'discount' => $detail->discount,
                        'interest_amount' => $detail->interest_amount,
                    ]);
                }

                TemporaryDetail::where('user_token', $token)->delete();

                return [$sale, $jsonChange];
            });
        } catch (BusinessRuleException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            throw new BusinessRuleException('No se pudo cerrar la venta. Intentá de nuevo.', 500);
        }

        try {
            app(SifenIssuer::class)->issueForSale($sale->fresh());
        } catch (\Throwable $e) {
            report($e);
        }

        return [
            'success' => true,
            'sale_id' => $sale->id,
            'change' => $change,
        ];
    }

    private function createInstallments(Sale $sale, array $data, float $totalToDistribute, int $installmentsCount): void
    {
        $customInstallmentAmount = parse_currency($data['installment_amount'] ?? 0);
        $frequency = $data['frequency'] ?? 'mensual';
        $installmentAmount = ($customInstallmentAmount > 0)
            ? $customInstallmentAmount
            : round($totalToDistribute / $installmentsCount);

        for ($i = 1; $i <= $installmentsCount; $i++) {
            $dueDate = Carbon::now();
            if ($frequency === 'semanal') {
                $dueDate->addWeeks($i);
            } elseif ($frequency === 'quincenal') {
                $dueDate->addDays($i * 15);
            } else {
                $dueDate->addMonths($i);
            }

            SaleInstallment::create([
                'sale_id' => $sale->id,
                'installment_number' => $i,
                'amount' => $installmentAmount,
                'due_date' => $dueDate->format('Y-m-d'),
                'status' => 0,
            ]);
        }
    }
}
