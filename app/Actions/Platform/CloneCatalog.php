<?php

namespace App\Actions\Platform;

use App\Exceptions\BusinessRuleException;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Modules\Products\Entities\Brand;
use Modules\Products\Entities\Category;
use Modules\Products\Entities\Product;

class CloneCatalog
{
    public function execute(Tenant $source, Tenant $destination, bool $copyPrices = true): CatalogCloneResult
    {
        $source = $source->fresh() ?? $source;
        $destination = $destination->fresh() ?? $destination;

        $this->assertCanClone($source, $destination);

        $snapshot = $source->run(fn () => $this->snapshot());

        if ($snapshot['products'] === []) {
            throw new BusinessRuleException('El origen no tiene productos para copiar.');
        }

        return $destination->run(function () use ($snapshot, $copyPrices) {
            return DB::transaction(function () use ($snapshot, $copyPrices) {
                return $this->write($snapshot, $copyPrices);
            });
        });
    }

    private function assertCanClone(Tenant $source, Tenant $destination): void
    {
        if ($source->is($destination) || $source->getTenantKey() === $destination->getTenantKey()) {
            throw new BusinessRuleException('Elegí otro comercio de origen.');
        }

        if ($source->provisioned_at === null) {
            throw new BusinessRuleException('El origen todavía se está aprovisionando.');
        }

        if ($destination->provisioned_at === null) {
            throw new BusinessRuleException('El destino todavía se está aprovisionando.');
        }
    }

    /**
     * @return array{categories: list<array<string, mixed>>, brands: list<array<string, mixed>>, products: list<array<string, mixed>>}
     */
    private function snapshot(): array
    {
        $disk = $this->files();

        return [
            'categories' => Category::query()->orderBy('id')->get()->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'status' => $category->status,
            ])->all(),
            'brands' => Brand::query()->orderBy('id')->get()->map(fn (Brand $brand) => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'country' => $brand->country,
                'description' => $brand->description,
                'status' => $brand->status,
            ])->all(),
            'products' => Product::query()->orderBy('id')->get()->map(function (Product $product) use ($disk) {
                $binary = null;
                $path = $product->image;
                if ($path && ! $product->usesDefaultImage()) {
                    $binary = $this->readImage($disk, $path);
                }

                return [
                    'code' => $product->code,
                    'description' => $product->description,
                    'price' => $product->price,
                    'cost' => $product->cost,
                    'category_id' => $product->category_id,
                    'brand_id' => $product->brand_id,
                    'status' => $product->status,
                    'model_name' => $product->model_name,
                    'warranty_months' => $product->warranty_months,
                    'image' => $binary !== null ? $path : Product::DEFAULT_IMAGE,
                    'image_binary' => $binary,
                ];
            })->all(),
        ];
    }

    /**
     * @param  array{categories: list<array<string, mixed>>, brands: list<array<string, mixed>>, products: list<array<string, mixed>>}  $snapshot
     */
    private function write(array $snapshot, bool $copyPrices): CatalogCloneResult
    {
        $ownerId = $this->ownerId();
        $categoryMap = $this->writeCategories($snapshot['categories'], $ownerId);
        $brandMap = $this->writeBrands($snapshot['brands']);

        $created = 0;
        $skipped = 0;
        $images = 0;
        $disk = $this->files();

        foreach ($snapshot['products'] as $row) {
            if (is_string($row['code']) && $row['code'] !== '' && Product::where('code', $row['code'])->exists()) {
                $skipped++;

                continue;
            }

            $image = Product::DEFAULT_IMAGE;
            if ($row['image_binary'] !== null) {
                $disk->put($row['image'], $row['image_binary'], 'public');
                $image = $row['image'];
                $images++;
            }

            Product::create([
                'code' => $row['code'] ?: null,
                'description' => $row['description'],
                'price' => $copyPrices ? $row['price'] : 0,
                'cost' => $copyPrices ? $row['cost'] : 0,
                'stock' => 0,
                'category_id' => $row['category_id'] ? ($categoryMap[$row['category_id']] ?? null) : null,
                'brand_id' => $row['brand_id'] ? ($brandMap[$row['brand_id']] ?? null) : null,
                'user_id' => $ownerId,
                'status' => $row['status'] ?? 1,
                'model_name' => $row['model_name'],
                'warranty_months' => $row['warranty_months'] ?? 12,
                'image' => $image,
            ]);
            $created++;
        }

        return new CatalogCloneResult(
            categories: count($categoryMap),
            brands: count($brandMap),
            products: $created,
            skipped: $skipped,
            images: $images,
        );
    }

    /**
     * @param  list<array<string, mixed>>  $categories
     * @return array<int, int>
     */
    private function writeCategories(array $categories, int $ownerId): array
    {
        $map = [];

        foreach ($categories as $category) {
            $existing = Category::query()->where('name', $category['name'])->first();
            if ($existing) {
                $map[$category['id']] = $existing->id;

                continue;
            }

            $created = Category::create([
                'name' => $category['name'],
                'user_id' => $ownerId,
                'status' => $category['status'] ?? 1,
            ]);
            $map[$category['id']] = $created->id;
        }

        return $map;
    }

    /**
     * @param  list<array<string, mixed>>  $brands
     * @return array<int, int>
     */
    private function writeBrands(array $brands): array
    {
        $map = [];

        foreach ($brands as $brand) {
            $existing = Brand::query()
                ->when(
                    filled($brand['slug']),
                    fn ($query) => $query->where('slug', $brand['slug']),
                    fn ($query) => $query->where('name', $brand['name']),
                )
                ->first()
                ?? Brand::query()->where('name', $brand['name'])->first();

            if ($existing) {
                $map[$brand['id']] = $existing->id;

                continue;
            }

            $created = Brand::create([
                'name' => $brand['name'],
                'slug' => $brand['slug'],
                'country' => $brand['country'],
                'description' => $brand['description'],
                'status' => $brand['status'] ?? 1,
            ]);
            $map[$brand['id']] = $created->id;
        }

        return $map;
    }

    private function ownerId(): int
    {
        $email = tenant()?->admin_email;
        $ownerId = $email
            ? User::query()->where('email', $email)->value('id')
            : null;

        $ownerId ??= User::query()->orderBy('id')->value('id');

        if (! $ownerId) {
            throw new BusinessRuleException('El destino no tiene un usuario para asignar los productos.');
        }

        return (int) $ownerId;
    }

    private function readImage(Filesystem $disk, string $path): ?string
    {
        if ($disk->exists($path)) {
            $binary = $disk->get($path);

            return is_string($binary) && $binary !== '' ? $binary : null;
        }

        if (method_exists($disk, 'path')) {
            $absolute = $disk->path($path);
            if (is_string($absolute) && is_file($absolute)) {
                return File::get($absolute);
            }
        }

        return null;
    }

    private function files(): Filesystem
    {
        Storage::forgetDisk($this->disk());

        return Storage::disk($this->disk());
    }

    private function disk(): string
    {
        return (string) config('media.public_disk', 'public');
    }
}
