<?php

namespace App\Http\Requests;

use App\Support\TenantAssignableRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public const PASSWORD_RULES = ['string', 'min:8', 'max:72'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => array_merge(['required'], self::PASSWORD_RULES),
            'role' => self::roleRules(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function roleRules(): array
    {
        return [
            'required',
            'string',
            Rule::exists('roles', 'name')->where('guard_name', 'web'),
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (TenantAssignableRole::isProtected(is_string($value) ? $value : null)) {
                    $fail('No se puede asignar el rol superadmin.');
                }
            },
        ];
    }
}
