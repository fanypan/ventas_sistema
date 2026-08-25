<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public const ASSIGNABLE_ROLES = ['admin', 'operator'];

    public const PASSWORD_RULES = ['string', 'min:8', 'max:72'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => array_merge(['required'], self::PASSWORD_RULES),
            'role' => ['required', 'string', Rule::in(self::ASSIGNABLE_ROLES)],
        ];
    }
}
