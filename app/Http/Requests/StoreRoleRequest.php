<?php

namespace App\Http\Requests;

use App\Support\TenantAssignableRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'guard_name' => 'web',
            'name' => trim((string) $this->input('name')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => $this->nameRules(),
            'guard_name' => ['required', 'in:web'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'permissions' => 'permisos',
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'El nombre solo puede tener letras, números, espacios, guiones o guión bajo.',
            'permissions.required' => 'Elegí al menos un permiso.',
            'permissions.min' => 'Elegí al menos un permiso.',
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function nameRules(?int $ignoreId = null): array
    {
        $unique = Rule::unique('roles', 'name')->where('guard_name', 'web');
        if ($ignoreId) {
            $unique->ignore($ignoreId);
        }

        return [
            'required',
            'string',
            'min:2',
            'max:50',
            'regex:/^[\p{L}0-9 _-]+$/u',
            'bail',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (TenantAssignableRole::isProtected(is_string($value) ? $value : null)) {
                    $fail('No se puede usar el rol superadmin.');
                }
            },
            $unique,
        ];
    }
}
