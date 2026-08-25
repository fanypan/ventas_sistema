<?php

namespace Modules\Sales\Services;

use App\Exceptions\BusinessRuleException;
use Modules\Products\Entities\Product;
use Modules\Sales\Entities\TemporaryDetail;

class CartService
{
    public function get(string $token): array
    {
        $details = TemporaryDetail::with('product')
            ->where('user_token', $token)
            ->get();

        $subTotal = $details->sum(function ($detail) {
            return $detail->quantity * $detail->price;
        });

        return [
            'details' => $details,
            'sub_total' => $subTotal,
        ];
    }

    public function add(string $token, int $productId, int $quantity): array
    {
        $product = Product::find($productId);
        if (! $product) {
            throw new BusinessRuleException('Product not found', 404);
        }

        $tempDetail = TemporaryDetail::where('user_token', $token)
            ->where('product_id', $productId)
            ->first();

        if ($tempDetail) {
            if ($product->stock < $quantity) {
                throw new BusinessRuleException('Stock insuficiente. Disponible: '.$product->stock);
            }
            $tempDetail->quantity += $quantity;
            $tempDetail->save();
            $product->decrement('stock', $quantity);
        } else {
            if ($product->stock < $quantity) {
                throw new BusinessRuleException('Stock insuficiente. Disponible: '.$product->stock);
            }
            TemporaryDetail::create([
                'user_token' => $token,
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $product->price,
                'cost' => $product->cost,
            ]);
            $product->decrement('stock', $quantity);
        }

        return $this->get($token);
    }

    public function remove(string $token, mixed $id): array
    {
        $detail = $this->line($token, $id);
        if ($detail) {
            $product = Product::find($detail->product_id);
            if ($product) {
                $product->increment('stock', $detail->quantity);
            }
            $detail->delete();
        }

        return $this->get($token);
    }

    public function update(string $token, mixed $id, int $quantity, float $discount, float $interest): array
    {
        $tempDetail = $this->line($token, $id);
        if ($tempDetail) {
            $product = Product::find($tempDetail->product_id);
            if ($product) {
                $diff = $quantity - $tempDetail->quantity;
                if ($diff > 0) {
                    if ($product->stock < $diff) {
                        throw new BusinessRuleException('Stock insuficiente para el ajuste');
                    }
                    $product->decrement('stock', $diff);
                } elseif ($diff < 0) {
                    $product->increment('stock', abs($diff));
                }
            }

            $tempDetail->quantity = $quantity;
            $tempDetail->discount = $discount;
            $tempDetail->interest_amount = $interest;
            $tempDetail->save();
        }

        return $this->get($token);
    }

    public function clear(string $token): void
    {
        $details = TemporaryDetail::where('user_token', $token)->get();
        foreach ($details as $detail) {
            $product = Product::find($detail->product_id);
            if ($product) {
                $product->increment('stock', $detail->quantity);
            }
            $detail->delete();
        }
    }

    public function line(string $token, mixed $id): ?TemporaryDetail
    {
        if ($id === null || $id === '') {
            return null;
        }

        return TemporaryDetail::where('user_token', $token)
            ->where('id', $id)
            ->first();
    }
}
