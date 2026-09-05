<?php

namespace Modules\StockAdjustments\Actions;

use App\Exceptions\BusinessRuleException;
use App\Models\InventoryAdjustment;
use Illuminate\Support\Facades\DB;
use Modules\Products\Entities\Product;

class AdjustInventory
{
    /**
     * @param  array{product_id: int, type: string, quantity: int, reason?: string|null, notes?: string|null}  $data
     * @return array{success: true, new_stock: int, message: string}
     */
    public function execute(array $data, int $userId): array
    {
        try {
            return DB::transaction(function () use ($data, $userId) {
                $product = Product::lockForUpdate()->find($data['product_id']);

                if ($product === null) {
                    throw new BusinessRuleException('No se encontró el producto.');
                }

                $quantity = (int) $data['quantity'];
                $type = $data['type'];

                if ($type === 'salida' && (int) $product->stock < $quantity) {
                    throw new BusinessRuleException('Stock insuficiente. Stock actual: '.$product->stock);
                }

                if ($type === 'entrada') {
                    $product->increment('stock', $quantity);
                } else {
                    $product->decrement('stock', $quantity);
                }

                InventoryAdjustment::create([
                    'product_id' => $product->id,
                    'user_id' => $userId,
                    'type' => $type,
                    'quantity' => $quantity,
                    'reason' => $data['reason'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);

                $product->refresh();

                return [
                    'success' => true,
                    'new_stock' => (int) $product->stock,
                    'message' => "Ajuste registrado. Nuevo stock de '{$product->description}': {$product->stock}",
                ];
            });
        } catch (BusinessRuleException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            throw new BusinessRuleException('No se pudo registrar el ajuste. Intentá de nuevo.');
        }
    }
}
