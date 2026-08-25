<?php

namespace App\Http\Requests\Platform;

use App\Rules\RucParaguay;
use App\Rules\TenantSlug;
use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:56', new TenantSlug, 'unique:tenants,slug'],
            'ruc' => ['nullable', 'string', 'max:30', new RucParaguay(allowConsumidorFinal: false)],
            'plan_id' => ['required', 'exists:plans,id'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email'],
            'interval' => ['required', 'in:monthly,yearly'],
            'brand_color' => ['nullable', 'string', 'max:20'],
        ];
    }
}
