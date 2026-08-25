<?php

namespace Modules\Products\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public const IMAGE_RULES = ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        merge_currency_fields($this, ['price', 'cost']);
    }

    public function rules(): array
    {
        return [
            'code' => ['nullable', 'string', 'unique:products,code'],
            'description' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'stock' => ['integer', 'min:0'],
            'model_name' => ['nullable', 'string', 'max:100'],
            'warranty_months' => ['nullable', 'integer', 'min:0'],
            'image' => self::IMAGE_RULES,
        ];
    }
}
