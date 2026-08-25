<?php

namespace Modules\Credits\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayInstallmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'installment_id' => ['required', 'exists:sale_installments,id'],
            'payment_method' => ['required', 'string'],
        ];
    }
}
