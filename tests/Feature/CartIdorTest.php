<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Products\Entities\Category;
use Modules\Products\Entities\Product;
use Modules\Sales\Entities\TemporaryDetail;
use Tests\TenantTestCase;

class CartIdorTest extends TenantTestCase
{
    public function test_cashier_cannot_remove_or_update_another_users_cart_line(): void
    {
        [$productId, $other] = $this->tenant->run(function () {
            $category = Category::create([
                'name' => 'IDOR',
                'user_id' => $this->tenantUser->id,
                'status' => 1,
            ]);

            $product = Product::create([
                'code' => 'IDOR-1',
                'description' => 'Item de carrito',
                'price' => 1000,
                'cost' => 400,
                'stock' => 10,
                'category_id' => $category->id,
                'user_id' => $this->tenantUser->id,
                'status' => 1,
            ]);

            $other = User::create([
                'name' => 'Otra caja',
                'email' => 'caja2@demo.test',
                'password' => Hash::make('password'),
            ]);
            $other->assignRole('admin');

            return [$product->id, $other];
        });

        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/sales/ajax/add-to-cart', [
                'product_id' => $productId,
                'quantity' => 2,
            ])
            ->assertOk();

        $lineId = $this->tenant->run(fn () => TemporaryDetail::value('id'));

        $this->flushSession();
        $this->app['auth']->forgetGuards();

        $this->actingAs($other)
            ->tenantPost('/admin/sales/ajax/remove-from-cart', ['id' => $lineId])
            ->assertOk();

        $this->actingAs($other)
            ->tenantPost('/admin/sales/ajax/update-cart-item', [
                'id' => $lineId,
                'quantity' => 1,
                'discount' => 0,
                'interest' => 0,
            ])
            ->assertOk();

        $this->tenant->run(function () use ($lineId, $productId) {
            $line = TemporaryDetail::find($lineId);
            $this->assertNotNull($line);
            $this->assertSame(2, $line->quantity);
            $this->assertSame(8, (int) Product::find($productId)->stock);
        });
    }

    public function test_cashier_can_remove_own_cart_line(): void
    {
        $productId = $this->tenant->run(function () {
            $category = Category::create([
                'name' => 'Propio',
                'user_id' => $this->tenantUser->id,
                'status' => 1,
            ]);

            return Product::create([
                'code' => 'OWN-1',
                'description' => 'Item propio',
                'price' => 2000,
                'cost' => 800,
                'stock' => 5,
                'category_id' => $category->id,
                'user_id' => $this->tenantUser->id,
                'status' => 1,
            ])->id;
        });

        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/sales/ajax/add-to-cart', [
                'product_id' => $productId,
                'quantity' => 1,
            ])
            ->assertOk();

        $lineId = $this->tenant->run(fn () => TemporaryDetail::value('id'));

        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/sales/ajax/remove-from-cart', ['id' => $lineId])
            ->assertOk();

        $this->tenant->run(function () use ($lineId, $productId) {
            $this->assertNull(TemporaryDetail::find($lineId));
            $this->assertSame(5, (int) Product::find($productId)->stock);
        });
    }
}
