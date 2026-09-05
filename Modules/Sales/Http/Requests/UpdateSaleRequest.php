<?php

namespace Modules\Sales\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date'],
            'customer_id' => ['required', 'exists:customers,id'],
            'payment_type' => ['required', 'in:efectivo,qr,tarjeta,transferencia,credito'],
            'status' => ['required', 'in:1,2,3'],
        ];
    }
}
