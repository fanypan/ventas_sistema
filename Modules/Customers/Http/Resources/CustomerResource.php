<?php

namespace Modules\Customers\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class CustomerResource extends JsonApiResource
{
    public array $attributes = [
        'nit',
        'name',
        'phone',
        'address',
        'status',
    ];

    public array $relationships = [];
}
