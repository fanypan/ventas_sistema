<?php

namespace Modules\Financials\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OpenCajaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        merge_currency_fields($this, ['monto_inicial']);
    }

    public function rules(): array
    {
        return [
            'monto_inicial' => ['required', 'numeric', 'min:0'],
        ];
    }
}
