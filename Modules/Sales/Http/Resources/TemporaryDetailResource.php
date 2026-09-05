<?php

namespace Modules\Sales\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;
use Modules\Products\Http\Resources\ProductResource;

class TemporaryDetailResource extends JsonApiResource
{
    public array $attributes = [
        'product_id',
        'quantity',
        'price',
        'cost',
        'discount',
        'interest_amount',
    ];

    public array $relationships = [
        'product' => ProductResource::class,
    ];

    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->includePreviouslyLoadedRelationships();
    }

    public function toAttributes(Request $request): array
    {
        return [
            'product_id' => $this->product_id,
            'quantity' => (int) $this->quantity,
            'price' => (float) $this->price,
            'cost' => (float) $this->cost,
            'discount' => (float) $this->discount,
            'interest_amount' => (float) $this->interest_amount,
        ];
    }
}
