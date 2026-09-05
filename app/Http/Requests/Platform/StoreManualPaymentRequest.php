<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;

class StoreManualPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('platform')?->can('payments.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1'],
            'method' => ['required', 'in:transferencia,efectivo'],
            'reference' => ['nullable', 'string', 'max:100'],
            'paid_at' => ['required', 'date'],
            'interval' => ['required', 'in:monthly,yearly'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'max:4096'],
        ];
    }
}
