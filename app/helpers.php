<?php

if (!function_exists('money')) {
    /**
     * Formatea un número al estilo Guaraníes (Gs.)
     * 
     * @param float|int $amount
     * @param bool $withSymbol
     * @return string
     */
    function money($amount, $withSymbol = true)
    {
        $formatted = number_format((float)$amount, 0, ',', '.');
        return $withSymbol ? 'Gs. ' . $formatted : $formatted;
    }
}

if (!function_exists('parse_currency')) {
    /**
     * Convierte un valor formateado (ej: "40.000") a número.
     * Replica la lógica de getCleanNumber() del frontend.
     */
    function parse_currency(mixed $value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if ($value === null || $value === '') {
            return 0.0;
        }

        $cleaned = str_replace('.', '', (string) $value);

        return is_numeric($cleaned) ? (float) $cleaned : 0.0;
    }
}

if (!function_exists('merge_currency_fields')) {
    /**
     * Normaliza campos monetarios del request antes de validar o guardar.
     */
    function merge_currency_fields(\Illuminate\Http\Request $request, array $fields): void
    {
        $merged = [];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $merged[$field] = parse_currency($request->input($field));
            }
        }

        if ($merged !== []) {
            $request->merge($merged);
        }
    }
}
