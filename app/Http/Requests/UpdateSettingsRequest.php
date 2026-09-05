<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'array'],
            'value' => ['required', 'array'],
            'tab' => ['nullable', 'string', 'max:50', 'regex:/^[a-z_]+$/'],
        ];
    }
}
