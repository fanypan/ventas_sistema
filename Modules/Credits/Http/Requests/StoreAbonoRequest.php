<?php

namespace Modules\Credits\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAbonoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        merge_currency_fields($this, ['amount', 'received_amount']);
    }

    public function rules(): array
    {
        return [
            'abonable_id' => ['required', 'integer'],
            'abonable_type' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string'],
            'reference' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
            'received_amount' => ['nullable', 'numeric'],
            'installment_number' => ['nullable', 'integer'],
        ];
    }
}
