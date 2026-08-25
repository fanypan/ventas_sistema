<?php

namespace Modules\Sales\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        merge_currency_fields($this, ['discount', 'interest']);
    }

    public function rules(): array
    {
        return [
            'id' => ['required'],
            'quantity' => ['required', 'integer', 'min:1'],
            'discount' => ['nullable', 'numeric'],
            'interest' => ['nullable', 'numeric'],
        ];
    }
}
