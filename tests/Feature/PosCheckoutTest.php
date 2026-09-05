<?php

namespace Tests\Feature;

use App\Http\Middleware\PreventRequestForgery;
use Modules\Financials\Entities\Caja;
use Modules\Products\Entities\Category;
use Modules\Products\Entities\Product;
use Modules\Sales\Entities\Sale;
use Modules\Sales\Entities\SaleInstallment;
use Modules\Sales\Entities\TemporaryDetail;
use Tests\TenantTestCase;

class PosCheckoutTest extends TenantTestCase
{
    protected function tenantPost(string $uri, array $data = [])
    {
        return $this->withHeaders([
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->withoutMiddleware(PreventRequestForgery::class)
            ->post('http://demo.localhost'.$uri, $data);
    }

    public function test_add_to_cart_rejects_missing_product(): void
    {
        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/sales/ajax/add-to-cart', [
                'product_id' => 99999,
                'quantity' => 1,
            ])
            ->assertNotFound()
            ->assertJson([
                'status' => 'error',
                'message' => 'Product not found',
                'data' => null,
            ]);
    }

    public function test_guest_cannot_add_to_cart(): void
    {
        $this->tenantPost('/admin/sales/ajax/add-to-cart', [
            'product_id' => 1,
            'quantity' => 1,
        ])
            ->assertUnauthorized()
            ->assertJson([
                'status' => 'error',
                'message' => 'Tenés que iniciar sesión.',
                'data' => null,
            ]);
    }

    public function test_add_to_cart_rejects_invalid_payload(): void
    {
        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/sales/ajax/add-to-cart', [])
            ->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonStructure(['status', 'message', 'data' => ['product_id', 'quantity']]);
    }

    public function test_add_to_cart_rejects_insufficient_stock(): void
    {
        $productId = $this->seedProduct(stock: 2);

        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/sales/ajax/add-to-cart', [
                'product_id' => $productId,
                'quantity' => 5,
            ])
            ->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Stock insuficiente. Disponible: 2',
                'data' => null,
            ]);
    }

    public function test_add_to_cart_reserves_stock_and_returns_cart(): void
    {
        $productId = $this->seedProduct(stock: 10, price: 1500);

        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/sales/ajax/add-to-cart', [
                'product_id' => $productId,
                'quantity' => 3,
            ])
            ->assertOk()
            ->assertJsonPath('meta.sub_total', 4500)
            ->assertJsonCount(1, 'data');

        $this->tenant->run(function () use ($productId) {
            $this->assertSame(7, (int) Product::find($productId)->stock);
            $this->assertSame(3, (int) TemporaryDetail::sum('quantity'));
        });
    }

    public function test_process_sale_rejects_empty_cart(): void
    {
        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/sales/ajax/process-sale', [
                'payment_type' => 'efectivo',
                'payment_with' => 10000,
            ])
            ->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'El carrito está vacío',
                'data' => null,
            ]);
    }

    public function test_process_sale_rejects_when_caja_is_closed(): void
    {
        $productId = $this->seedProduct(stock: 5, price: 10000);

        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/sales/ajax/add-to-cart', [
                'product_id' => $productId,
                'quantity' => 1,
            ])
            ->assertOk();

        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/sales/ajax/process-sale', [
                'payment_type' => 'efectivo',
                'payment_with' => 10000,
            ])
            ->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Abrí tu caja para vender. Finanzas → Cajas.',
                'data' => null,
            ]);
    }

    public function test_process_sale_rejects_cash_shortfall(): void
    {
        $productId = $this->seedProduct(stock: 5, price: 10000);
        $this->openCaja();

        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/sales/ajax/add-to-cart', [
                'product_id' => $productId,
                'quantity' => 1,
            ])
            ->assertOk();

        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/sales/ajax/process-sale', [
                'payment_type' => 'efectivo',
                'payment_with' => 5000,
            ])
            ->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'El monto pagado no cubre el total',
                'data' => null,
            ]);
    }

    public function test_process_sale_cash_creates_sale_and_clears_cart(): void
    {
        $productId = $this->seedProduct(stock: 5, price: 10000);
        $this->openCaja();

        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/sales/ajax/add-to-cart', [
                'product_id' => $productId,
                'quantity' => 1,
            ])
            ->assertOk();

        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/sales/ajax/process-sale', [
                'payment_type' => 'efectivo',
                'payment_with' => 15000,
                'discount' => 0,
            ])
            ->assertCreated()
            ->assertJsonPath('data.type', 'sales')
            ->assertJsonPath('data.meta.change', 5000);

        $this->tenant->run(function () use ($productId) {
            $sale = Sale::first();
            $this->assertNotNull($sale);
            $this->assertSame(10000, (int) $sale->total);
            $this->assertSame('efectivo', $sale->payment_type);
            $this->assertSame(1, (int) $sale->status);
            $this->assertSame(1, $sale->details()->count());
            $this->assertSame(0, TemporaryDetail::count());
            $this->assertSame(4, (int) Product::find($productId)->stock);
        });
    }

    public function test_process_sale_credit_creates_installments(): void
    {
        $productId = $this->seedProduct(stock: 5, price: 30000);
        $this->openCaja();

        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/sales/ajax/add-to-cart', [
                'product_id' => $productId,
                'quantity' => 1,
            ])
            ->assertOk();

        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/sales/ajax/process-sale', [
                'payment_type' => 'credito',
                'installments' => 3,
                'frequency' => 'mensual',
                'discount' => 0,
            ])
            ->assertCreated()
            ->assertJsonPath('data.type', 'sales')
            ->assertJsonPath('data.meta.change', 0);

        $this->tenant->run(function () {
            $sale = Sale::first();
            $this->assertNotNull($sale);
            $this->assertSame(2, (int) $sale->status);
            $this->assertSame('credito', $sale->payment_type);
            $this->assertSame(0, (int) $sale->payment_with);
            $this->assertSame(3, SaleInstallment::count());
            $this->assertSame(10000, (int) SaleInstallment::first()->amount);
        });
    }

    private function seedProduct(int $stock = 10, int $price = 10000): int
    {
        return $this->tenant->run(function () use ($stock, $price) {
            $category = Category::create([
                'name' => 'POS',
                'user_id' => $this->tenantUser->id,
                'status' => 1,
            ]);

            return Product::create([
                'code' => 'POS-'.$stock.'-'.$price,
                'description' => 'Item POS',
                'price' => $price,
                'cost' => (int) round($price / 2),
                'stock' => $stock,
                'category_id' => $category->id,
                'user_id' => $this->tenantUser->id,
                'status' => 1,
            ])->id;
        });
    }

    private function openCaja(): void
    {
        $this->tenant->run(function () {
            Caja::create([
                'user_id' => $this->tenantUser->id,
                'opening_amount' => 0,
                'closing_amount' => 0,
                'opened_at' => now(),
                'status' => 1,
            ]);
        });
    }
}
