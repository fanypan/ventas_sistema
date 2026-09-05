<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Products\Entities\Product;
use Modules\Sales\Database\factories\SaleDetailFactory;

class SaleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
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

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    protected static function newFactory()
    {
        return SaleDetailFactory::new();
    }
}
