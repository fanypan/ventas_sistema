<?php

namespace App\Http\Requests;

class UpdatePermissionRequest extends StorePermissionRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'id' => ['required', 'integer'],
        ]);
    }
}
