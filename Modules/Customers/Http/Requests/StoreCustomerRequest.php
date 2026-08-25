<?php

namespace Modules\Customers\Http\Requests;

use App\Rules\RucParaguay;
use App\Support\RucParaguay as RucParaguaySupport;
use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nit' => ['nullable', new RucParaguay, 'unique:customers,nit'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function customerPayload(?int $userId): array
    {
        $data = $this->validated();
        $data['nit'] = RucParaguaySupport::format($data['nit'] ?? null);
        $data['user_id'] = $userId;
        $data['status'] = 1;

        return $data;
    }
}
