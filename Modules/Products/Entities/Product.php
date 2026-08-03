<?php

namespace Modules\Products\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

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
    
    protected static function newFactory()
    {
        return \Modules\Products\Database\factories\ProductFactory::new();
    }
}
