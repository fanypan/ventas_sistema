<?php

namespace Modules\Products\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
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
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function imageUrl(): string
    {
        $path = $this->image ?: self::DEFAULT_IMAGE;

        return asset('storage/' . $path);
    }

    public function usesDefaultImage(): bool
    {
        return ! $this->image || $this->image === self::DEFAULT_IMAGE;
    }
    
    protected static function newFactory()
    {
        return \Modules\Products\Database\factories\ProductFactory::new();
    }
}
