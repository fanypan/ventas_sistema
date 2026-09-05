<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Products\Entities\Category;
use Modules\Products\Entities\Product;
use Tests\TenantTestCase;

class ProductImageUploadTest extends TenantTestCase
{
    public function test_svg_product_image_is_rejected(): void
    {
        $categoryId = $this->tenant->run(function () {
            return Category::create([
                'name' => 'General',
                'user_id' => $this->tenantUser->id,
                'status' => 1,
            ])->id;
        });

        $svg = UploadedFile::fake()->create('payload.svg', 20, 'image/svg+xml');

        $this->actingAs($this->tenantUser)
            ->from('http://demo.localhost/admin/products/create')
            ->tenantPost('/admin/products', [
                'description' => 'Producto SVG',
                'price' => 1000,
                'cost' => 500,
                'category_id' => $categoryId,
                'stock' => 1,
                'image' => $svg,
            ])
            ->assertRedirect('http://demo.localhost/admin/products/create')
            ->assertSessionHasErrors('image');

        $this->tenant->run(function () {
            $this->assertDatabaseMissing('products', ['description' => 'Producto SVG']);
        });
    }

    public function test_jpeg_product_image_is_accepted(): void
    {
        $categoryId = $this->tenant->run(function () {
            return Category::create([
                'name' => 'Fotos',
                'user_id' => $this->tenantUser->id,
                'status' => 1,
            ])->id;
        });

        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/products', [
                'description' => 'Producto JPEG',
                'price' => 2000,
                'cost' => 800,
                'category_id' => $categoryId,
                'stock' => 1,
                'image' => UploadedFile::fake()->image('foto.jpg'),
            ])
            ->assertRedirect();

        $this->tenant->run(function () {
            $product = Product::where('description', 'Producto JPEG')->first();
            $this->assertNotNull($product);
            $this->assertNotNull($product->image);
            $this->assertStringStartsWith('products/', $product->image);
            $this->assertStringEndsWith('.jpg', $product->image);
            $this->assertFalse($product->usesDefaultImage());
            Storage::disk(config('media.public_disk'))->assertExists($product->image);
        });
    }
}
