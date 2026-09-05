<?php

namespace Modules\Financials\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        merge_currency_fields($this, ['amount']);
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'type' => ['required', 'in:gasto,insumo'],
            'insumo_id' => ['required_if:type,insumo', 'nullable', 'exists:insumos,id'],
            'quantity' => ['required_if:type,insumo', 'nullable', 'numeric', 'min:0.01'],
            'new_insumo' => ['nullable', 'boolean'],
        ];
    }
}
