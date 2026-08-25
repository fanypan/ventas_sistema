<?php

namespace Modules\Suppliers\Http\Requests;

use App\Rules\RucParaguay;
use App\Support\RucParaguay as RucParaguaySupport;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nit' => ['nullable', new RucParaguay(allowConsumidorFinal: false), 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function supplierPayload(): array
    {
        $data = $this->validated();
        $data['nit'] = RucParaguaySupport::format($data['nit'] ?? null);

        return $data;
    }
}
