<?php

namespace Modules\Sales\Actions;

use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;
use Modules\Products\Entities\Product;
use Modules\Sales\Entities\Sale;

class VoidSale
{
    public function execute(Sale $sale): void
    {
        try {
            DB::transaction(function () use ($sale) {
                $locked = Sale::lockForUpdate()->find($sale->id);

                if ($locked === null) {
                    throw new BusinessRuleException('No se encontró la venta.');
                }

                if ((int) $locked->status === 0) {
                    throw new BusinessRuleException('Esta venta ya ha sido anulada.');
                }

                $locked->loadMissing('details');

                foreach ($locked->details as $detail) {
                    $product = Product::lockForUpdate()->find($detail->product_id);
                    if ($product === null) {
                        continue;
                    }

                    $product->increment('stock', $detail->quantity);
                }

                if ($locked->payment_type === 'credito') {
                    $locked->installments()->update(['status' => 2]);
                }

                $locked->status = 0;
                $locked->save();
            });
        } catch (BusinessRuleException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            throw new BusinessRuleException('No se pudo anular la venta. Intentá de nuevo.');
        }
    }
}
