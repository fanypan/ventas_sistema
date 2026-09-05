<?php

use App\Helpers\SettingHelper;
use App\Services\Billing\PlanLimitService;
use App\Services\Media\MediaUrl;
use Illuminate\Http\Request;

if (! function_exists('money')) {
    /**
     * Formatea un número al estilo Guaraníes (Gs.)
     *
     * @param  float|int  $amount
     * @param  bool  $withSymbol
     * @return string
     */
    function money($amount, $withSymbol = true)
    {
        $formatted = number_format((float) $amount, 0, ',', '.');

        return $withSymbol ? 'Gs. '.$formatted : $formatted;
    }
}

if (! function_exists('parse_currency')) {
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

if (! function_exists('merge_currency_fields')) {
    /**
     * Normaliza campos monetarios del request antes de validar o guardar.
     */
    function merge_currency_fields(Request $request, array $fields): void
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

if (! function_exists('platform_can')) {
    function platform_can(string $permission): bool
    {
        $user = auth('platform')->user();

        return $user !== null && $user->can($permission);
    }
}

if (! function_exists('plan_has')) {
    function plan_has(string $feature): bool
    {
        return app(PlanLimitService::class)->hasFeature($feature);
    }
}

if (! function_exists('setting_file_url')) {
    function setting_file_url(?string $value = null, ?string $key = null): string
    {
        if ($value === null && $key !== null) {
            $value = SettingHelper::getValue($key);
        }

        return app(MediaUrl::class)->settingUrl($value);
    }
}
