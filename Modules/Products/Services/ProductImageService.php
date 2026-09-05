<?php

namespace Modules\Products\Services;

use App\Services\Media\ImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Products\Entities\Product;

class ProductImageService
{
    public function __construct(private ImageOptimizer $optimizer) {}

    public function storeUploaded(?UploadedFile $file): string
    {
        if (! $file) {
            return Product::DEFAULT_IMAGE;
        }

        return $this->store($file);
    }

    public function replace(Product $product, UploadedFile $file): string
    {
        $this->deleteCustom($product);

        return $this->store($file);
    }

    public function deleteCustom(Product $product): void
    {
        if ($product->image && ! $product->usesDefaultImage()) {
            Storage::disk($this->disk())->delete($product->image);
        }
    }

    private function store(UploadedFile $file): string
    {
        $encoded = $this->optimizer->encode($file);
        Storage::disk($this->disk())->put($encoded->path, $encoded->binary, 'public');

        return $encoded->path;
    }

    private function disk(): string
    {
        return (string) config('media.public_disk', 'public');
    }
}
