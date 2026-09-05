<?php

namespace Tests\Feature;

use App\Http\Middleware\PreventRequestForgery;
use App\Models\InventoryAdjustment;
use Modules\Products\Entities\Category;
use Modules\Products\Entities\Product;
use Tests\TenantTestCase;

class StockAdjustmentTest extends TenantTestCase
{
    protected function tenantPost(string $uri, array $data = [])
    {
        return $this->withHeaders([
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->withoutMiddleware(PreventRequestForgery::class)
            ->post('http://demo.localhost'.$uri, $data);
    }

    public function test_entrada_increments_stock_and_records_history(): void
    {
        $productId = $this->seedProduct(stock: 4);

        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/stock-adjustments', [
                'product_id' => $productId,
                'type' => 'entrada',
                'quantity' => 3,
                'reason' => 'Inventario físico',
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'new_stock' => 7,
            ]);

        $this->tenant->run(function () use ($productId) {
            $this->assertSame(7, (int) Product::find($productId)->stock);
            $this->assertSame(1, InventoryAdjustment::count());
            $this->assertSame('entrada', InventoryAdjustment::first()->type);
        });
    }

    public function test_salida_decrements_stock(): void
    {
        $productId = $this->seedProduct(stock: 10);

        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/stock-adjustments', [
                'product_id' => $productId,
                'type' => 'salida',
                'quantity' => 4,
                'reason' => 'Merma / Daño',
            ])
            ->assertOk()
            ->assertJsonPath('new_stock', 6);

        $this->tenant->run(function () use ($productId) {
            $this->assertSame(6, (int) Product::find($productId)->stock);
        });
    }

    public function test_salida_rejects_insufficient_stock(): void
    {
        $productId = $this->seedProduct(stock: 2);

        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/stock-adjustments', [
                'product_id' => $productId,
                'type' => 'salida',
                'quantity' => 5,
            ])
            ->assertStatus(422)
            ->assertJson(['error' => 'Stock insuficiente. Stock actual: 2']);

        $this->tenant->run(function () use ($productId) {
            $this->assertSame(2, (int) Product::find($productId)->stock);
            $this->assertSame(0, InventoryAdjustment::count());
        });
    }

    private function seedProduct(int $stock): int
    {
        return $this->tenant->run(function () use ($stock) {
            $category = Category::create([
                'name' => 'Ajuste',
                'user_id' => $this->tenantUser->id,
                'status' => 1,
            ]);

            return Product::create([
                'code' => 'ADJ-'.$stock,
                'description' => 'Item ajuste',
                'price' => 1000,
                'cost' => 500,
                'stock' => $stock,
                'category_id' => $category->id,
                'user_id' => $this->tenantUser->id,
                'status' => 1,
            ])->id;
        });
    }
}
