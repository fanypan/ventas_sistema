<?php

namespace App\Http\Requests\Platform;

use App\Support\PlatformAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlatformUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('platform')?->can('users.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:platform_users,email'],
            'password' => ['required', 'string', 'min:8', 'max:72'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [
                'string',
                Rule::exists('roles', 'name')->where('guard_name', PlatformAccess::GUARD),
            ],
        ];
    }
}
