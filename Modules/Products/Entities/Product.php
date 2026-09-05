<?php

namespace Modules\Products\Entities;

use App\Models\Concerns\HasActiveStatus;
use App\Models\User;
use App\Services\Media\MediaUrl;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Products\Database\factories\ProductFactory;

class Product extends Model
{
    use HasActiveStatus;
    use HasFactory;

    public const DEFAULT_IMAGE = 'products/default.png';

    protected $fillable = [
        'code',
        'description',
        'price',
        'cost',
        'stock',
        'category_id',
        'user_id',
        'status',
        'brand_id',
        'model_name',
        'warranty_months',
        'image',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function imageUrl(): string
    {
        if ($this->usesDefaultImage()) {
            return asset(config('media.placeholder'));
        }

        return app(MediaUrl::class)->publicUrl($this->image);
    }

    public function usesDefaultImage(): bool
    {
        return ! $this->image || $this->image === self::DEFAULT_IMAGE;
    }

    #[Scope]
    protected function lowStock(Builder $query, int $threshold = 5): void
    {
        $query->where('stock', '<=', $threshold);
    }

    #[Scope]
    protected function zeroStock(Builder $query): void
    {
        $query->where('stock', '<=', 0);
    }

    #[Scope]
    protected function inStock(Builder $query): void
    {
        $query->where('stock', '>', 0);
    }

    protected static function newFactory()
    {
        return ProductFactory::new();
    }
}
