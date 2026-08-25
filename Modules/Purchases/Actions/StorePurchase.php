<?php

namespace Modules\Purchases\Actions;

use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;
use Modules\Products\Entities\Product;
use Modules\Purchases\Entities\Purchase;
use Modules\Purchases\Entities\PurchaseDetail;

class StorePurchase
{
    public function execute(array $data, int $userId): Purchase
    {
        $items = collect($data['items'])->map(function ($item) {
            $item['price'] = (int) round(parse_currency($item['price'] ?? 0));
            $item['quantity'] = (int) ($item['quantity'] ?? 0);
            $item['lot'] = trim((string) ($item['lot'] ?? '')) ?: null;
            $item['expiration'] = ! empty($item['expiration']) ? $item['expiration'] : null;

            return $item;
        });

        if ($items->contains(fn ($item) => $item['quantity'] < 1 || $item['price'] < 0)) {
            throw new BusinessRuleException('Hay ítems con cantidad o costo inválido.');
        }

        try {
            return DB::transaction(function () use ($data, $items, $userId) {
                $total = $items->sum(fn ($item) => $item['quantity'] * $item['price']);

                $purchase = Purchase::create([
                    'supplier_id' => $data['supplier_id'],
                    'user_id' => $userId,
                    'total' => $total,
                    'status' => 1,
                ]);

                foreach ($items as $item) {
                    PurchaseDetail::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'expiration_date' => $item['expiration'],
                        'lot_number' => $item['lot'],
                    ]);

                    $product = Product::lockForUpdate()->find($item['id']);
                    if (! $product) {
                        continue;
                    }

                    $product->stock = (int) $product->stock + $item['quantity'];
                    $product->cost = $item['price'];
                    $product->save();
                }

                return $purchase;
            });
        } catch (BusinessRuleException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            throw new BusinessRuleException('No se pudo guardar la compra. Intentá de nuevo.', 500);
        }
    }
}
