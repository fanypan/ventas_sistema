<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        return $this->belongsTo(\Modules\Products\Entities\Product::class);
    }
    
    protected static function newFactory()
    {
        return \Modules\Sales\Database\factories\TemporaryDetailFactory::new();
    }
}
