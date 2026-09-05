<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => array_merge(['required', 'confirmed'], StoreUserRequest::PASSWORD_RULES),
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'Ingresá una contraseña.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña tiene que tener al menos 8 caracteres.',
            'password.max' => 'La contraseña es demasiado larga.',
        ];
    }
}
