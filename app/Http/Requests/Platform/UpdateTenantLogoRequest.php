<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantLogoRequest extends FormRequest
{
    public const RULES = ['file', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'];

    public function authorize(): bool
    {
        return $this->user('platform')?->can('tenants.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'logo' => array_merge(['required'], self::RULES),
        ];
    }

    public function messages(): array
    {
        return [
            'logo.required' => 'Elegí un archivo de logo.',
            'logo.mimes' => 'El logo tiene que ser JPG, PNG, GIF o WebP.',
            'logo.max' => 'El logo no puede pesar más de 2 MB.',
        ];
    }
}
