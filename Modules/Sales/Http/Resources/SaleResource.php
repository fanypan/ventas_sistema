<?php

namespace Modules\Sales\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class SaleResource extends JsonApiResource
{
    public array $attributes = [
        'customer_id',
        'total',
        'discount',
        'interest_amount',
        'payment_type',
        'payment_with',
        'change',
        'status',
    ];

    public array $relationships = [];

    public function __construct($resource, private readonly float|int|null $jsonChange = null)
    {
        parent::__construct($resource);
    }

    public function toMeta(Request $request): array
    {
        if ($this->jsonChange === null) {
            return [];
        }

        return ['change' => $this->jsonChange];
    }
}
