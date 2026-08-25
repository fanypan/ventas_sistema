<?php

namespace Modules\Products\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Products\Entities\Product;

class ProductImageService
{
    public function ensureDefault(): void
    {
        $path = storage_path('app/public/'.Product::DEFAULT_IMAGE);

        if (file_exists($path)) {
            return;
        }

        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $fallback = storage_path('app/public/logo.png');
        if (file_exists($fallback)) {
            copy($fallback, $path);
        }
    }

    public function storeUploaded(?UploadedFile $file): string
    {
        $this->ensureDefault();

        if ($file) {
            return $file->store('products', 'public');
        }

        return Product::DEFAULT_IMAGE;
    }

    public function replace(Product $product, UploadedFile $file): string
    {
        if ($product->image && ! $product->usesDefaultImage()) {
            Storage::disk('public')->delete($product->image);
        }

        return $file->store('products', 'public');
    }

    public function deleteCustom(Product $product): void
    {
        if ($product->image && ! $product->usesDefaultImage()) {
            Storage::disk('public')->delete($product->image);
        }
    }
}
