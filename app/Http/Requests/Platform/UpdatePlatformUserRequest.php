<?php

namespace App\Http\Requests\Platform;

use App\Support\PlatformAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlatformUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('platform')?->can('users.update') ?? false;
    }

    public function rules(): array
    {
        $user = $this->route('platformUser');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('platform_users', 'email')->ignore($user),
            ],
            'password' => ['nullable', 'string', 'min:8', 'max:72'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [
                'string',
                Rule::exists('roles', 'name')->where('guard_name', PlatformAccess::GUARD),
            ],
        ];
    }
}
