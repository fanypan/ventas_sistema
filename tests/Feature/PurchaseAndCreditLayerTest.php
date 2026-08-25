<?php

namespace Tests\Feature;

use Modules\Financials\Entities\Caja;
use Modules\Products\Entities\Category;
use Modules\Products\Entities\Product;
use Modules\Purchases\Entities\Purchase;
use Modules\Sales\Entities\Sale;
use Modules\Suppliers\Entities\Supplier;
use Tests\TenantTestCase;

class PurchaseAndCreditLayerTest extends TenantTestCase
{
    public function test_purchase_store_increments_stock_and_cost(): void
    {
        [$productId, $supplierId] = $this->seedProductAndSupplier(stock: 2, cost: 1000);

        $this->actingAs($this->tenantUser)
            ->withHeaders(['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'])
            ->tenantPost('/admin/purchases', [
                'supplier_id' => $supplierId,
                'items' => [
                    [
                        'id' => $productId,
                        'quantity' => 3,
                        'price' => '5.000',
                    ],
                ],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->tenant->run(function () use ($productId) {
            $product = Product::find($productId);
            $this->assertSame(5, (int) $product->stock);
            $this->assertSame(5000, (int) $product->cost);
            $this->assertSame(1, Purchase::count());
        });
    }

    public function test_purchase_destroy_restores_stock(): void
    {
        [$productId, $supplierId] = $this->seedProductAndSupplier(stock: 2, cost: 1000);

        $this->actingAs($this->tenantUser)
            ->withHeaders(['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'])
            ->tenantPost('/admin/purchases', [
                'supplier_id' => $supplierId,
                'items' => [
                    [
                        'id' => $productId,
                        'quantity' => 4,
                        'price' => 2000,
                    ],
                ],
            ])
            ->assertOk();

        $purchaseId = $this->tenant->run(fn () => Purchase::value('id'));

        $this->actingAs($this->tenantUser)
            ->from('http://demo.localhost/admin/purchases')
            ->tenantPost("/admin/purchases/{$purchaseId}", ['_method' => 'DELETE'])
            ->assertRedirect();

        $this->tenant->run(function () use ($productId, $purchaseId) {
            $this->assertSame(2, (int) Product::find($productId)->stock);
            $this->assertSame(0, (int) Purchase::find($purchaseId)->status);
        });
    }

    public function test_store_abono_requires_open_caja(): void
    {
        $saleId = $this->seedCreditSale();

        $this->actingAs($this->tenantUser)
            ->from('http://demo.localhost/admin/credits/receivables')
            ->tenantPost('/admin/credits/abono', [
                'abonable_id' => $saleId,
                'abonable_type' => Sale::class,
                'amount' => 1000,
                'payment_method' => 'efectivo',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_store_abono_pays_credit_sale(): void
    {
        $saleId = $this->seedCreditSale(total: 10000);
        $this->tenant->run(function () {
            Caja::create([
                'user_id' => $this->tenantUser->id,
                'opening_amount' => 0,
                'closing_amount' => 0,
                'opened_at' => now(),
                'status' => 1,
            ]);
        });

        $this->actingAs($this->tenantUser)
            ->from('http://demo.localhost/admin/credits/receivables')
            ->tenantPost('/admin/credits/abono', [
                'abonable_id' => $saleId,
                'abonable_type' => Sale::class,
                'amount' => 10000,
                'payment_method' => 'efectivo',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->tenant->run(function () use ($saleId) {
            $this->assertSame(1, (int) Sale::find($saleId)->status);
        });
    }

    private function seedProductAndSupplier(int $stock, int $cost): array
    {
        return $this->tenant->run(function () use ($stock, $cost) {
            $category = Category::create([
                'name' => 'Compras',
                'user_id' => $this->tenantUser->id,
                'status' => 1,
            ]);

            $product = Product::create([
                'code' => 'COM-1',
                'description' => 'Item compra',
                'price' => $cost * 2,
                'cost' => $cost,
                'stock' => $stock,
                'category_id' => $category->id,
                'user_id' => $this->tenantUser->id,
                'status' => 1,
            ]);

            $supplier = Supplier::create([
                'name' => 'Proveedor Test',
                'status' => 1,
            ]);

            return [$product->id, $supplier->id];
        });
    }

    private function seedCreditSale(int $total = 10000): int
    {
        return $this->tenant->run(function () use ($total) {
            return Sale::create([
                'user_id' => $this->tenantUser->id,
                'customer_id' => \Modules\Customers\Entities\Customer::first()->id,
                'total' => $total,
                'discount' => 0,
                'payment_with' => 0,
                'change' => 0,
                'payment_type' => 'credito',
                'status' => 2,
            ])->id;
        });
    }
}
