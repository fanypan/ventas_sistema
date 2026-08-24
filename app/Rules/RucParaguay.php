<?php

namespace App\Rules;

use App\Support\RucParaguay as RucParaguaySupport;
use Illuminate\Contracts\Validation\Rule;

class RucParaguay implements Rule
{
    public function __construct(
        private bool $allowConsumidorFinal = true,
    ) {
    }

    public function passes($attribute, $value): bool
    {
        return RucParaguaySupport::isValid(
            $value === null ? null : (string) $value,
            $this->allowConsumidorFinal,
        );
    }

    public function message(): string
    {
        return 'El RUC/NIT no tiene un formato válido o el dígito verificador es incorrecto.';
    }
}
