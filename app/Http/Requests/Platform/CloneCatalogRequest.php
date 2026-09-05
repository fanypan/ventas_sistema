<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CloneCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('platform')?->can('tenants.catalog') ?? false;
    }

    public function rules(): array
    {
        $destination = $this->route('tenant');

        return [
            'source_id' => [
                'required',
                'string',
                Rule::exists('tenants', 'id')->whereNotNull('provisioned_at'),
                Rule::notIn([(string) $destination->getTenantKey()]),
            ],
            'copy_prices' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'source_id.required' => 'Elegí de qué comercio copiar el catálogo.',
            'source_id.exists' => 'Ese comercio no está listo para copiar.',
            'source_id.not_in' => 'Elegí otro comercio de origen.',
        ];
    }
}
