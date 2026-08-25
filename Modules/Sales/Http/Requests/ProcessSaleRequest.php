<?php

namespace Modules\Sales\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        merge_currency_fields($this, ['payment_with', 'discount', 'interest_value', 'installment_amount']);
    }

    public function rules(): array
    {
        return [
            'payment_type' => ['nullable', 'string'],
            'payment_with' => ['nullable', 'numeric'],
            'discount' => ['nullable', 'numeric'],
            'interest_type' => ['nullable', 'string'],
            'interest_value' => ['nullable', 'numeric'],
            'installments' => ['nullable', 'integer', 'min:1'],
            'frequency' => ['nullable', 'string'],
            'installment_amount' => ['nullable', 'numeric'],
            'customer_id' => ['nullable', 'integer'],
            'reference_number' => ['nullable', 'string'],
            'payment_note' => ['nullable', 'string'],
        ];
    }
}
