<?php

namespace Modules\Financials\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloseCajaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        merge_currency_fields($this, ['monto_final']);
    }

    public function rules(): array
    {
        return [
            'monto_final' => ['required', 'numeric', 'min:0'],
        ];
    }
}
