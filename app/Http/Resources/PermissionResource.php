<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class PermissionResource extends JsonApiResource
{
    public array $attributes = [
        'name',
        'guard_name',
    ];

    public array $relationships = [];
}
