<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Financials\Entities\Caja;
use Modules\Products\Entities\Category;
use Modules\Products\Entities\Product;
use Modules\Sales\Entities\Sale;
use Tests\TenantTestCase;

class PlanLimitsTest extends TenantTestCase
{
    public function test_starter_cannot_open_purchases(): void
    {
        $this->usePlan('starter');

        $this->actingAs($this->tenantUser)
            ->tenantGet('/admin/purchases')
            ->assertRedirect('http://demo.localhost/admin/dashboard');
    }

    public function test_starter_cannot_store_purchase(): void
    {
        $this->usePlan('starter');

        $this->actingAs($this->tenantUser)
            ->withHeaders(['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'])
            ->tenantPost('/admin/purchases', ['supplier_id' => 1, 'items' => []])
            ->assertForbidden()
            ->assertJsonFragment(['message' => 'Tu plan no incluye compras y proveedores. Contactá a AranduTech para ampliar.']);
    }

    public function test_starter_cannot_open_credits(): void
    {
        $this->usePlan('starter');

        $this->actingAs($this->tenantUser)
            ->tenantGet('/admin/credits/receivables')
            ->assertRedirect('http://demo.localhost/admin/dashboard');
    }

    public function test_starter_cannot_sell_on_credit(): void
    {
        $this->usePlan('starter');
        $productId = $this->seedProduct();
        $this->openCajaFor($this->tenantUser->id);

        $this->actingAs($this->tenantUser)
            ->withHeaders(['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'])
            ->tenantPost('/admin/sales/ajax/add-to-cart', [
                'product_id' => $productId,
                'quantity' => 1,
            ])
            ->assertOk();

        $this->actingAs($this->tenantUser)
            ->withHeaders(['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'])
            ->tenantPost('/admin/sales/ajax/process-sale', [
                'payment_type' => 'credito',
                'installments' => 2,
            ])
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'Tu plan no incluye créditos y ventas a crédito. Contactá a AranduTech para ampliar.']);
    }

    public function test_starter_can_sell_cash(): void
    {
        $this->usePlan('starter');
        $productId = $this->seedProduct(price: 10000);
        $this->openCajaFor($this->tenantUser->id);

        $this->actingAs($this->tenantUser)
            ->withHeaders(['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'])
            ->tenantPost('/admin/sales/ajax/add-to-cart', [
                'product_id' => $productId,
                'quantity' => 1,
            ])
            ->assertOk();

        $this->actingAs($this->tenantUser)
            ->withHeaders(['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'])
            ->tenantPost('/admin/sales/ajax/process-sale', [
                'payment_type' => 'efectivo',
                'payment_with' => 10000,
            ])
            ->assertCreated()
            ->assertJsonPath('data.type', 'sales');
    }

    public function test_user_cannot_open_a_second_caja(): void
    {
        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/cajas', ['monto_inicial' => 1000])
            ->assertRedirect();

        $this->actingAs($this->tenantUser)
            ->from('http://demo.localhost/admin/cajas/create')
            ->tenantPost('/admin/cajas', ['monto_inicial' => 2000])
            ->assertRedirect();

        $this->tenant->run(function () {
            $this->assertSame(1, Caja::open()->count());
        });
    }

    public function test_sale_uses_the_logged_in_user_caja(): void
    {
        $operator = $this->makeOperator();
        $productId = $this->seedProduct(price: 5000);

        $operatorCajaId = $this->openCajaFor($operator->id);
        $adminCajaId = $this->openCajaFor($this->tenantUser->id);

        $this->actingAs($this->tenantUser)
            ->withHeaders(['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'])
            ->tenantPost('/admin/sales/ajax/add-to-cart', [
                'product_id' => $productId,
                'quantity' => 1,
            ])
            ->assertOk();

        $this->actingAs($this->tenantUser)
            ->withHeaders(['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'])
            ->tenantPost('/admin/sales/ajax/process-sale', [
                'payment_type' => 'efectivo',
                'payment_with' => 5000,
            ])
            ->assertCreated();

        $this->tenant->run(function () use ($adminCajaId, $operatorCajaId) {
            $sale = Sale::first();
            $this->assertNotNull($sale);
            $this->assertSame($adminCajaId, (int) $sale->cash_id);
            $this->assertNotSame($operatorCajaId, (int) $sale->cash_id);
        });
    }

    public function test_starter_blocks_a_second_open_caja_for_another_user(): void
    {
        $this->usePlan('starter');
        $this->openCajaFor($this->tenantUser->id);
        $operator = $this->makeOperator();

        $this->actingAs($operator)
            ->from('http://demo.localhost/admin/cajas/create')
            ->tenantPost('/admin/cajas', ['monto_inicial' => 500])
            ->assertRedirect();

        $this->tenant->run(function () {
            $this->assertSame(1, Caja::open()->count());
        });
    }

    public function test_starter_blocks_creating_more_users_than_the_plan(): void
    {
        $this->usePlan('starter');

        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/user', [
                'name' => 'Cajero uno',
                'email' => 'cajero1@demo.test',
                'password' => '12345678',
                'role' => 'operator',
            ])
            ->assertRedirect();

        $this->actingAs($this->tenantUser)
            ->from('http://demo.localhost/admin/user')
            ->tenantPost('/admin/user', [
                'name' => 'Cajero dos',
                'email' => 'cajero2@demo.test',
                'password' => '12345678',
                'role' => 'operator',
            ])
            ->assertRedirect();

        $this->tenant->run(function () {
            $this->assertSame(2, User::count());
            $this->assertFalse(User::where('email', 'cajero2@demo.test')->exists());
        });
    }

    private function usePlan(string $slug): void
    {
        $planId = Plan::where('slug', $slug)->value('id');
        $this->tenant->update(['plan_id' => $planId]);
        $this->tenant->unsetRelation('plan');
    }

    private function makeOperator(): User
    {
        return $this->tenant->run(function () {
            $user = User::create([
                'name' => 'Cajero',
                'email' => 'cajero@demo.test',
                'password' => Hash::make('password'),
                'must_change_password' => false,
            ]);
            $user->assignRole('operator');

            return $user;
        });
    }

    private function seedProduct(int $stock = 10, int $price = 10000): int
    {
        return $this->tenant->run(function () use ($stock, $price) {
            $category = Category::create([
                'name' => 'Plan',
                'user_id' => $this->tenantUser->id,
                'status' => 1,
            ]);

            return Product::create([
                'code' => 'PLAN-'.$price,
                'description' => 'Item plan',
                'price' => $price,
                'cost' => (int) round($price / 2),
                'stock' => $stock,
                'category_id' => $category->id,
                'user_id' => $this->tenantUser->id,
                'status' => 1,
            ])->id;
        });
    }

    private function openCajaFor(int $userId): int
    {
        return $this->tenant->run(function () use ($userId) {
            return Caja::create([
                'user_id' => $userId,
                'opening_amount' => 0,
                'closing_amount' => 0,
                'opened_at' => now(),
                'status' => 1,
            ])->id;
        });
    }
}
