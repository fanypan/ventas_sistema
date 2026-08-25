<?php

namespace Modules\Sales\Http\Requests;

use App\Rules\RucParaguay;
use App\Support\RucParaguay as RucParaguaySupport;
use Illuminate\Foundation\Http\FormRequest;

class StorePosCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('nit')) {
            $this->merge([
                'nit' => RucParaguaySupport::format($this->input('nit')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required'],
            'nit' => ['required', new RucParaguay, 'unique:customers,nit'],
            'phone' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
        ];
    }
}
