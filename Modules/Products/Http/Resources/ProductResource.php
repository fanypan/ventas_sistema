<?php

namespace Modules\Products\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ProductResource extends JsonApiResource
{
    public array $attributes = [
        'code',
        'description',
        'price',
        'cost',
        'stock',
        'status',
        'category_id',
        'brand_id',
        'model_name',
        'warranty_months',
    ];

    public array $relationships = [];
}
