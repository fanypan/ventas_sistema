<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class UserResource extends JsonApiResource
{
    public array $attributes = [
        'name',
        'email',
        'role',
        'created_at',
        'updated_at',
    ];

    public array $relationships = [];

    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'role' => implode(',', $this->getRoleNames()->toArray()),
            'created_at' => date('d-m-Y H:i:s', strtotime((string) $this->created_at)),
            'updated_at' => date('d-m-Y H:i:s', strtotime((string) $this->updated_at)),
        ];
    }
}
