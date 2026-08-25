<?php

namespace Modules\{Module}\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Store{Model}Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
