<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;

class DestroyTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('platform')?->can('tenants.delete') ?? false;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'current_password:platform'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'Ingresá tu contraseña para confirmar.',
            'password.current_password' => 'La contraseña es incorrecta.',
        ];
    }

    public function attributes(): array
    {
        return [
            'password' => 'contraseña',
        ];
    }
}
