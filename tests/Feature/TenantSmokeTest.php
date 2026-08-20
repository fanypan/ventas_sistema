<?php

namespace Tests\Feature;

use Modules\Customers\Entities\Customer;
use Modules\Products\Entities\Category;
use Modules\Products\Entities\Product;
use Modules\Sales\Entities\Sale;
use Tests\TenantTestCase;

class TenantSmokeTest extends TenantTestCase
{
    public function test_tenant_login_page_and_auth(): void
    {
        $this->tenantGet('/login')->assertOk();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('http://demo.localhost/login', [
                'email' => 'admin@demo.test',
                'password' => 'password',
            ])
            ->assertRedirect();
    }

    public function test_can_create_product_and_sale(): void
    {
        $this->tenant->run(function () {
            $user = $this->tenantUser;
            $category = Category::create([
                'name' => 'General',
                'user_id' => $user->id,
                'status' => 1,
            ]);

            $product = Product::create([
                'code' => 'P-001',
                'description' => 'Producto de prueba',
                'price' => 10000,
                'cost' => 5000,
                'stock' => 5,
                'category_id' => $category->id,
                'user_id' => $user->id,
                'status' => 1,
            ]);

            $this->assertDatabaseHas('products', ['code' => 'P-001']);

            $customer = Customer::first();
            $sale = Sale::create([
                'user_id' => $user->id,
                'customer_id' => $customer->id,
                'total' => 10000,
                'discount' => 0,
                'payment_with' => 10000,
                'change' => 0,
                'payment_type' => 'efectivo',
                'status' => 1,
            ]);

            $this->assertDatabaseHas('sales', ['id' => $sale->id, 'total' => 10000]);
            $this->assertSame('Producto de prueba', $product->fresh()->description);
        });
    }

    public function test_product_store_via_http(): void
    {
        $categoryId = $this->tenant->run(function () {
            return Category::create([
                'name' => 'Bebidas',
                'user_id' => $this->tenantUser->id,
                'status' => 1,
            ])->id;
        });

        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/products', [
                'description' => 'Coca 2L',
                'price' => 12000,
                'cost' => 8000,
                'category_id' => $categoryId,
                'stock' => 3,
            ])
            ->assertRedirect();

        $this->tenant->run(function () {
            $this->assertDatabaseHas('products', ['description' => 'Coca 2L']);
        });
    }
}
