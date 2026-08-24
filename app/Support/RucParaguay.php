<?php

namespace App\Support;

final class RucParaguay
{
    private const BASE_MAX = 11;

    /**
     * Valida formato y dígito verificador (módulo 11, algoritmo DNIT).
     */
    public static function isValid(?string $input, bool $allowConsumidorFinal = true): bool
    {
        if ($input === null || trim($input) === '') {
            return true;
        }

        $value = self::normalizeInput($input);

        if ($allowConsumidorFinal && $value === '0') {
            return true;
        }

        $parsed = self::parse($value);

        if ($parsed === null) {
            return false;
        }

        return (int) $parsed['dv'] === self::calculateDv($parsed['base']);
    }

    /**
     * Devuelve el RUC canónico `base-dv` o `0` para consumidor final.
     */
    public static function format(?string $input): ?string
    {
        if ($input === null || trim($input) === '') {
            return null;
        }

        $value = self::normalizeInput($input);

        if ($value === '0') {
            return '0';
        }

        $parsed = self::parse($value);

        if ($parsed === null || ! self::isValid($value)) {
            return trim($input);
        }

        return $parsed['base'].'-'.$parsed['dv'];
    }

    /**
     * @return array{base: string, dv: string}|null
     */
    public static function parse(string $input): ?array
    {
        $value = self::normalizeInput($input);

        if ($value === '' || $value === '0') {
            return null;
        }

        if (str_contains($value, '-')) {
            $parts = explode('-', $value);
            if (count($parts) !== 2) {
                return null;
            }

            [$base, $dv] = $parts;
            $base = self::sanitizeBase($base);
            $dv = trim($dv);

            if ($base === '' || ! preg_match('/^\d$/', $dv)) {
                return null;
            }

            return ['base' => $base, 'dv' => $dv];
        }

        if (! preg_match('/^[0-9A-Za-z]+$/', $value) || strlen($value) < 2) {
            return null;
        }

        $dv = substr($value, -1);
        $base = self::sanitizeBase(substr($value, 0, -1));

        if ($base === '' || ! preg_match('/^\d$/', $dv)) {
            return null;
        }

        return ['base' => $base, 'dv' => $dv];
    }

    public static function calculateDv(string $base): int
    {
        $numeroAl = '';

        for ($i = 0; $i < strlen($base); $i++) {
            $char = strtoupper($base[$i]);
            $code = ord($char);

            if ($code < 48 || $code > 57) {
                $numeroAl .= (string) $code;
            } else {
                $numeroAl .= $char;
            }
        }

        $k = 2;
        $total = 0;

        for ($i = strlen($numeroAl) - 1; $i >= 0; $i--) {
            if ($k > self::BASE_MAX) {
                $k = 2;
            }

            $total += (int) $numeroAl[$i] * $k;
            $k++;
        }

        $resto = $total % self::BASE_MAX;

        return $resto > 1 ? self::BASE_MAX - $resto : 0;
    }

    private static function normalizeInput(string $input): string
    {
        return strtoupper(str_replace(' ', '', trim($input)));
    }

    private static function sanitizeBase(string $base): string
    {
        $base = strtoupper(trim($base));

        if ($base === '' || ! preg_match('/^[0-9A-Z]+$/', $base) || strlen($base) > 15) {
            return '';
        }

        return $base;
    }
}
