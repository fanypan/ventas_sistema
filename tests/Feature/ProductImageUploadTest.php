<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Modules\Products\Entities\Category;
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
            $this->assertDatabaseHas('products', ['description' => 'Producto JPEG']);
        });
    }
}
