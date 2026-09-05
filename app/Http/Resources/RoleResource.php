<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class RoleResource extends JsonApiResource
{
    public array $attributes = [
        'name',
        'guard_name',
    ];

    public array $relationships = [
        'permissions' => PermissionResource::class,
    ];
}
