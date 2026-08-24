<?php

namespace App\Rules;

use App\Support\TenantDatabaseName;
use Illuminate\Contracts\Validation\Rule;

class TenantSlug implements Rule
{
    public function passes($attribute, $value): bool
    {
        return is_string($value) && TenantDatabaseName::slugIsValid($value);
    }

    public function message(): string
    {
        return 'El slug solo puede tener letras minúsculas y números, sin guion ni guion bajo.';
    }
}
