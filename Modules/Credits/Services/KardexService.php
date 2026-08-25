<?php

namespace Modules\Credits\Services;

use Carbon\Carbon;
use Modules\Purchases\Entities\Purchase;
use Modules\Sales\Entities\Sale;

class KardexService
{
    public function customerMovements(int $customerId, $from = null, $to = null)
    {
        $sales = Sale::with(['abonos.user', 'creator'])
            ->where('customer_id', $customerId)
            ->where(function ($q) {
                $q->where('payment_type', 'credito')
                    ->orWhereIn('status', [2, 3])
                    ->orWhereHas('abonos');
            })
            ->orderBy('created_at')
            ->get();

        $rows = collect();
        foreach ($sales as $sale) {
            $rows->push([
                'date' => $sale->created_at,
                'type' => 'factura',
                'ref' => $sale->id,
                'description' => 'Venta #'.$sale->id,
                'cargo' => (float) $sale->total,
                'abono' => 0,
                'user' => $sale->creator->name ?? '-',
                'abono_id' => null,
            ]);
            foreach ($sale->abonos as $abono) {
                $rows->push([
                    'date' => $abono->payment_date ?? $abono->created_at,
                    'type' => 'abono',
                    'ref' => $sale->id,
                    'description' => 'Abono venta #'.$sale->id.($abono->payment_method ? ' ('.$abono->payment_method.')' : ''),
                    'cargo' => 0,
                    'abono' => (float) $abono->amount,
                    'user' => $abono->user->name ?? '-',
                    'abono_id' => $abono->id,
                ]);
            }
        }

        return $this->runningBalance($rows, $from, $to);
    }

    public function supplierMovements(int $supplierId, $from = null, $to = null)
    {
        $purchases = Purchase::with(['abonos.user', 'creator'])
            ->where('supplier_id', $supplierId)
            ->where(function ($q) {
                $q->where('status', 2)->orWhereHas('abonos');
            })
            ->orderBy('created_at')
            ->get();

        $rows = collect();
        foreach ($purchases as $purchase) {
            $rows->push([
                'date' => $purchase->created_at,
                'type' => 'compra',
                'ref' => $purchase->id,
                'description' => 'Compra #'.$purchase->id,
                'cargo' => (float) $purchase->total,
                'abono' => 0,
                'user' => $purchase->creator->name ?? '-',
                'abono_id' => null,
            ]);
            foreach ($purchase->abonos as $abono) {
                $rows->push([
                    'date' => $abono->payment_date ?? $abono->created_at,
                    'type' => 'pago',
                    'ref' => $purchase->id,
                    'description' => 'Pago compra #'.$purchase->id.($abono->payment_method ? ' ('.$abono->payment_method.')' : ''),
                    'cargo' => 0,
                    'abono' => (float) $abono->amount,
                    'user' => $abono->user->name ?? '-',
                    'abono_id' => $abono->id,
                ]);
            }
        }

        return $this->runningBalance($rows, $from, $to);
    }

    private function runningBalance($rows, $from, $to)
    {
        $rows = $rows->sortBy(function ($r) {
            return Carbon::parse($r['date'])->timestamp;
        })->values();

        if ($from) {
            $rows = $rows->filter(fn ($r) => Carbon::parse($r['date'])->toDateString() >= $from)->values();
        }
        if ($to) {
            $rows = $rows->filter(fn ($r) => Carbon::parse($r['date'])->toDateString() <= $to)->values();
        }

        $saldo = 0;

        return $rows->map(function ($row) use (&$saldo) {
            $saldo += $row['cargo'] - $row['abono'];
            $row['saldo'] = $saldo;

            return $row;
        });
    }
}
