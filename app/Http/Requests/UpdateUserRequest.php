<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->input('id'))],
            'password' => array_merge(['nullable'], StoreUserRequest::PASSWORD_RULES),
            'role' => ['required', 'string', Rule::in(StoreUserRequest::ASSIGNABLE_ROLES)],
        ];
    }
}
