<?php

namespace App\Support;

final class BackupSchedule
{
    /**
     * Horas 24h (HH:MM) a partir de una lista separada por comas.
     * Vacío o inválido = no programar (solo dumps a mano).
     *
     * @return list<string>
     */
    public static function times(string $raw): array
    {
        $seen = [];

        foreach (explode(',', $raw) as $part) {
            $at = trim($part);
            if ($at === '' || ! preg_match('/^(\d{1,2}):([0-5]\d)$/', $at, $matches)) {
                continue;
            }

            $hour = (int) $matches[1];
            if ($hour > 23) {
                continue;
            }

            $normalized = sprintf('%02d:%02d', $hour, (int) $matches[2]);
            $seen[$normalized] = true;
        }

        return array_keys($seen);
    }
}
