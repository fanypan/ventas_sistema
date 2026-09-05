<?php

namespace Modules\Purchases\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class PurchaseResource extends JsonApiResource
{
    public array $attributes = [
        'supplier_id',
        'total',
        'status',
    ];

    public array $relationships = [];
}
