<?php

namespace App\Http\Requests\Platform;

use App\Rules\RucParaguay;
use App\Rules\TenantSlug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('platform')?->can('tenants.create') ?? false;
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
            'interval' => ['required', 'in:monthly,yearly,lifetime'],
            'brand_color' => ['nullable', 'string', 'max:20'],
            'logo' => array_merge(['nullable'], UpdateTenantLogoRequest::RULES),
            'catalog_source_id' => [
                'nullable',
                'string',
                Rule::exists('tenants', 'id')->whereNotNull('provisioned_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'logo.mimes' => 'El logo tiene que ser JPG, PNG, GIF o WebP.',
            'logo.max' => 'El logo no puede pesar más de 2 MB.',
        ];
    }
}
