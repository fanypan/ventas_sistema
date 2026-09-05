<?php

namespace Tests\Feature;

use Modules\Customers\Entities\Customer;
use Modules\Products\Entities\Category;
use Modules\Products\Entities\Product;
use Modules\Sales\Entities\Sale;
use Modules\Sales\Entities\SaleDetail;
use Modules\Sales\Entities\SaleInstallment;
use Tests\TenantTestCase;

class UserFacingExceptionTest extends TenantTestCase
{
    public function test_voiding_a_sale_restores_stock_and_hides_exception_text(): void
    {
        $saleId = $this->tenant->run(function () {
            $category = Category::create([
                'name' => 'Anular',
                'user_id' => $this->tenantUser->id,
                'status' => 1,
            ]);

            $product = Product::create([
                'code' => 'VOID-1',
                'description' => 'Item a anular',
                'price' => 5000,
                'cost' => 2000,
                'stock' => 3,
                'category_id' => $category->id,
                'user_id' => $this->tenantUser->id,
                'status' => 1,
            ]);

            $sale = Sale::create([
                'user_id' => $this->tenantUser->id,
                'customer_id' => Customer::first()->id,
                'total' => 5000,
                'discount' => 0,
                'payment_with' => 5000,
                'change' => 0,
                'payment_type' => 'efectivo',
                'status' => 1,
            ]);

            SaleDetail::create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'quantity' => 2,
                'price' => 5000,
                'cost' => 2000,
            ]);

            $product->decrement('stock', 2);

            return $sale->id;
        });

        $this->actingAs($this->tenantUser)
            ->from('http://demo.localhost/admin/sales')
            ->tenantPost("/admin/sales/{$saleId}/void")
            ->assertRedirect('http://demo.localhost/admin/sales')
            ->assertSessionHas('success')
            ->assertSessionMissing('error');

        $this->tenant->run(function () use ($saleId) {
            $this->assertSame(0, (int) Sale::find($saleId)->status);
            $this->assertSame(3, (int) Product::where('code', 'VOID-1')->value('stock'));
        });

        $this->actingAs($this->tenantUser)
            ->from('http://demo.localhost/admin/sales')
            ->tenantPost("/admin/sales/{$saleId}/void")
            ->assertRedirect('http://demo.localhost/admin/sales')
            ->assertSessionHas('error', 'Esta venta ya ha sido anulada.');
    }

    public function test_voiding_a_credit_sale_cancels_installments(): void
    {
        $saleId = $this->tenant->run(function () {
            $category = Category::create([
                'name' => 'Crédito',
                'user_id' => $this->tenantUser->id,
                'status' => 1,
            ]);

            $product = Product::create([
                'code' => 'VOID-CRED',
                'description' => 'Item crédito',
                'price' => 9000,
                'cost' => 3000,
                'stock' => 5,
                'category_id' => $category->id,
                'user_id' => $this->tenantUser->id,
                'status' => 1,
            ]);

            $sale = Sale::create([
                'user_id' => $this->tenantUser->id,
                'customer_id' => Customer::first()->id,
                'total' => 9000,
                'discount' => 0,
                'payment_with' => 0,
                'change' => 0,
                'payment_type' => 'credito',
                'installments_count' => 3,
                'status' => 2,
            ]);

            SaleDetail::create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => 9000,
                'cost' => 3000,
            ]);

            $product->decrement('stock', 1);

            foreach ([1, 2, 3] as $number) {
                SaleInstallment::create([
                    'sale_id' => $sale->id,
                    'installment_number' => $number,
                    'amount' => 3000,
                    'due_date' => now()->addMonths($number)->toDateString(),
                    'status' => 0,
                ]);
            }

            return $sale->id;
        });

        $this->actingAs($this->tenantUser)
            ->from('http://demo.localhost/admin/sales')
            ->tenantPost("/admin/sales/{$saleId}/void")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->tenant->run(function () use ($saleId) {
            $this->assertSame(0, (int) Sale::find($saleId)->status);
            $this->assertSame(5, (int) Product::where('code', 'VOID-CRED')->value('stock'));
            $this->assertSame(3, SaleInstallment::where('sale_id', $saleId)->cancelled()->count());
        });
    }

    public function test_sale_purchase_and_credit_failures_do_not_return_exception_text(): void
    {
        $files = [
            base_path('Modules/Sales/Http/Controllers/SaleController.php'),
            base_path('Modules/Purchases/Http/Controllers/PurchaseController.php'),
            base_path('Modules/Credits/Http/Controllers/CreditController.php'),
        ];

        foreach ($files as $file) {
            $source = file_get_contents($file);
            $this->assertDoesNotMatchRegularExpression(
                '/(with\(|json\()[^;]*getMessage\(\)/s',
                $source,
                basename($file).' no debe devolver getMessage() al usuario'
            );
        }
    }
}
