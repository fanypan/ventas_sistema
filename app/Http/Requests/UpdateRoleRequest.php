<?php

namespace App\Http\Requests;

class UpdateRoleRequest extends StoreRoleRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'id' => ['required', 'integer'],
            'name' => $this->nameRules((int) $this->input('id')),
        ]);
    }
}
