<?php

namespace Modules\Purchases\Actions;

use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;
use Modules\Products\Entities\Product;
use Modules\Purchases\Entities\Purchase;
use Modules\Purchases\Entities\PurchaseDetail;

class DestroyPurchase
{
    public function execute(Purchase $purchase): void
    {
        if ((int) $purchase->status === Purchase::STATUS_VOIDED) {
            throw new BusinessRuleException('Esta compra ya ha sido anulada.');
        }

        $purchase->loadMissing('details.product');

        try {
            DB::transaction(function () use ($purchase) {
                foreach ($purchase->details as $detail) {
                    $product = Product::lockForUpdate()->find($detail->product_id);
                    if (! $product) {
                        continue;
                    }

                    $product->decrement('stock', $detail->quantity);
                    $this->restoreLastPurchaseCost($product, $purchase->id);
                }

                $purchase->status = Purchase::STATUS_VOIDED;
                $purchase->save();
            });
        } catch (BusinessRuleException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            throw new BusinessRuleException('No se pudo anular la compra. Intentá de nuevo.');
        }
    }

    private function restoreLastPurchaseCost(Product $product, int $exceptPurchaseId): void
    {
        $last = PurchaseDetail::where('product_id', $product->id)
            ->whereHas('purchase', function ($query) use ($exceptPurchaseId) {
                $query->paid()->where('id', '!=', $exceptPurchaseId);
            })
            ->latest('id')
            ->first();

        if ($last) {
            $product->cost = (int) round($last->price);
            $product->save();
        }
    }
}
