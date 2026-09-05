<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Products\Entities\Product;
use Modules\Sales\Database\factories\TemporaryDetailFactory;

class TemporaryDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_token',
        'product_id',
        'quantity',
        'cost',
        'price',
        'discount',
        'interest_amount',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function newFactory()
    {
        return TemporaryDetailFactory::new();
    }
}
