<?php

namespace Modules\Financials\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInsumoConsumptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'insumo_id' => ['required', 'exists:insumos,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
