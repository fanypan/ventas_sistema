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
