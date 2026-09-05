<?php

namespace App\Http\Requests\Platform;

use App\Support\PlatformAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlatformRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('platform')?->can('roles.update') ?? false;
    }

    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name' => [
                'required',
                'string',
                'max:56',
                'regex:/^[a-z0-9-]+$/',
                Rule::notIn(['superadmin']),
                Rule::unique('roles', 'name')
                    ->where('guard_name', PlatformAccess::GUARD)
                    ->ignore($role),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [Rule::in(PlatformAccess::names())],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Usá minúsculas, números y guiones.',
            'name.not_in' => 'Ese nombre de rol está reservado.',
        ];
    }
}
