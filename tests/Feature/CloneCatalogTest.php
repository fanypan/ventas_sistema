<?php

namespace Tests\Feature;

use App\Actions\Platform\CloneCatalog;
use App\Exceptions\BusinessRuleException;
use App\Http\Middleware\PreventRequestForgery;
use App\Models\Plan;
use App\Models\PlatformUser;
use App\Models\Tenant;
use App\Models\User;
use App\Support\PlatformAccess;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\Products\Entities\Brand;
use Modules\Products\Entities\Category;
use Modules\Products\Entities\Product;
use Tests\TenantTestCase;

class CloneCatalogTest extends TenantTestCase
{
    public function test_clone_copies_catalog_with_stock_zero_and_keeps_source_intact(): void
    {
        $this->seedSourceCatalog(stock: 15, price: 8000, cost: 5000);

        $destination = $this->provisionDestination();

        $result = app(CloneCatalog::class)->execute($this->tenant, $destination);

        $this->assertSame(1, $result->products);
        $this->assertSame(0, $result->skipped);
        $this->assertSame(1, $result->images);

        $this->tenant->run(function () {
            $product = Product::where('code', '7791234567890')->firstOrFail();
            $this->assertSame(15, (int) $product->stock);
            $this->assertSame(8000, (int) $product->price);
            Storage::disk((string) config('media.public_disk', 'public'))->assertExists('products/coca.jpg');
        });

        $destination->run(function () {
            $this->assertSame(1, Product::count());
            $product = Product::where('code', '7791234567890')->firstOrFail();
            $this->assertSame(0, (int) $product->stock);
            $this->assertSame(8000, (int) $product->price);
            $this->assertSame(5000, (int) $product->cost);
            $this->assertSame('Coca 2L', $product->description);
            $this->assertSame('Bebidas', $product->category->name);
            $this->assertSame('Coca-Cola', $product->brand()->value('name'));
            $this->assertSame('products/coca.jpg', $product->image);
            Storage::disk((string) config('media.public_disk', 'public'))->assertExists('products/coca.jpg');
            $this->assertSame('img-bytes', Storage::disk((string) config('media.public_disk', 'public'))->get('products/coca.jpg'));
        });
    }

    public function test_clone_can_omit_prices_and_skips_existing_codes(): void
    {
        $this->seedSourceCatalog();
        $destination = $this->provisionDestination();

        $destination->run(function () {
            $userId = User::query()->value('id');
            $category = Category::create([
                'name' => 'Ya estaba',
                'user_id' => $userId,
                'status' => 1,
            ]);
            Product::create([
                'code' => '7791234567890',
                'description' => 'Viejo',
                'price' => 1,
                'cost' => 1,
                'stock' => 9,
                'category_id' => $category->id,
                'user_id' => $userId,
                'status' => 1,
            ]);
        });

        $result = app(CloneCatalog::class)->execute($this->tenant, $destination, copyPrices: false);

        $this->assertSame(0, $result->products);
        $this->assertSame(1, $result->skipped);

        $destination->run(function () {
            $product = Product::where('code', '7791234567890')->firstOrFail();
            $this->assertSame('Viejo', $product->description);
            $this->assertSame(9, (int) $product->stock);
            $this->assertSame(1, (int) $product->price);
        });
    }

    public function test_clone_without_prices_sets_zero_on_new_products(): void
    {
        $this->seedSourceCatalog();
        $destination = $this->provisionDestination();

        app(CloneCatalog::class)->execute($this->tenant, $destination, copyPrices: false);

        $destination->run(function () {
            $product = Product::where('code', '7791234567890')->firstOrFail();
            $this->assertSame(0, (int) $product->price);
            $this->assertSame(0, (int) $product->cost);
            $this->assertSame(0, (int) $product->stock);
        });
    }

    public function test_cannot_clone_onto_the_same_tenant(): void
    {
        $this->seedSourceCatalog();

        $this->expectException(BusinessRuleException::class);

        app(CloneCatalog::class)->execute($this->tenant, $this->tenant);
    }

    public function test_staff_can_copy_catalog_from_the_platform(): void
    {
        $this->seedSourceCatalog();
        $destination = $this->provisionDestination();
        $path = config('saas.platform_path');

        $this->actingAs(PlatformUser::first(), 'platform')
            ->get("/{$path}/clientes/{$destination->id}")
            ->assertOk()
            ->assertSee('Copiar catálogo')
            ->assertSee($this->tenant->name);

        $this->actingAs(PlatformUser::first(), 'platform')
            ->from("/{$path}/clientes/{$destination->id}")
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post("/{$path}/clientes/{$destination->id}/catalogo", [
                'source_id' => $this->tenant->id,
                'copy_prices' => '1',
            ])
            ->assertRedirect("/{$path}/clientes/{$destination->id}");

        $destination->run(function () {
            $this->assertSame(1, Product::count());
            $this->assertSame(0, (int) Product::first()->stock);
        });
    }

    public function test_create_tenant_can_seed_catalog_from_another_comercio(): void
    {
        $this->seedSourceCatalog();
        $path = config('saas.platform_path');

        $this->actingAs(PlatformUser::first(), 'platform')
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post("/{$path}/clientes", [
                'name' => 'Despensa Nueva',
                'slug' => 'despensanueva',
                'plan_id' => Plan::first()->id,
                'admin_name' => 'Ada',
                'admin_email' => 'ada@nueva.test',
                'interval' => 'monthly',
                'catalog_source_id' => $this->tenant->id,
            ])
            ->assertRedirect();

        $destination = Tenant::where('slug', 'despensanueva')->firstOrFail();
        $this->rememberTenantArtifact($destination->getTenantKey());
        $this->assertNull($destination->catalog_source_id);

        $destination->run(function () {
            $this->assertSame(1, Product::count());
            $product = Product::where('code', '7791234567890')->firstOrFail();
            $this->assertSame(0, (int) $product->stock);
            $this->assertSame(8000, (int) $product->price);
        });
    }

    public function test_billing_cannot_copy_catalog(): void
    {
        $destination = $this->provisionDestination();
        $path = config('saas.platform_path');
        $billing = $this->billing();

        $this->actingAs($billing, 'platform')
            ->get("/{$path}/clientes/{$destination->id}")
            ->assertOk()
            ->assertDontSee('Copiar catálogo')
            ->assertDontSee('Copiar desde');

        $this->actingAs($billing, 'platform')
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post("/{$path}/clientes/{$destination->id}/catalogo", [
                'source_id' => $this->tenant->id,
            ])
            ->assertForbidden();
    }

    private function seedSourceCatalog(int $stock = 15, int $price = 8000, int $cost = 5000): void
    {
        $this->tenant->run(function () use ($stock, $price, $cost) {
            Storage::disk((string) config('media.public_disk', 'public'))->put('products/coca.jpg', 'img-bytes');
            Storage::disk((string) config('media.public_disk', 'public'))->assertExists('products/coca.jpg');

            $category = Category::create([
                'name' => 'Bebidas',
                'user_id' => $this->tenantUser->id,
                'status' => 1,
            ]);
            $brand = Brand::create([
                'name' => 'Coca-Cola',
                'slug' => 'coca-cola',
                'country' => 'Paraguay',
                'status' => 1,
            ]);

            Product::create([
                'code' => '7791234567890',
                'description' => 'Coca 2L',
                'price' => $price,
                'cost' => $cost,
                'stock' => $stock,
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'user_id' => $this->tenantUser->id,
                'status' => 1,
                'image' => 'products/coca.jpg',
            ]);
        });
    }

    private function provisionDestination(string $slug = 'despensa2'): Tenant
    {
        $tenant = Tenant::create([
            'name' => 'Despensa 2',
            'slug' => $slug,
            'status' => Tenant::STATUS_PENDING,
            'plan_id' => Plan::first()->id,
            'admin_name' => 'Admin Destino',
            'admin_email' => $slug.'@dest.test',
        ]);
        $this->rememberTenantArtifact($tenant->getTenantKey());

        return $tenant->fresh();
    }

    private function billing(): PlatformUser
    {
        $user = PlatformUser::create([
            'name' => 'Cobros',
            'email' => 'billing@arandutech.com',
            'password' => Hash::make('secret'),
            'role' => PlatformAccess::ROLE_BILLING,
        ]);
        $user->assignRole(PlatformAccess::ROLE_BILLING);

        return $user;
    }
}
