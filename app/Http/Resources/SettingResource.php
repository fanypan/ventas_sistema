<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class SettingResource extends JsonApiResource
{
    public array $attributes = [
        'key',
        'value',
        'name',
        'type',
        'category',
    ];

    public array $relationships = [];
}
