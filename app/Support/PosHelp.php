<?php

namespace App\Support;

final class PosHelp
{
    /**
     * Combos that browsers or the OS already own. The POS never binds these.
     *
     * @var list<string>
     */
    public const BROWSER_RESERVED = [
        'F1',
        'F3',
        'F5',
        'F6',
        'F7',
        'F10',
        'F11',
        'F12',
        'Ctrl+R',
        'Ctrl+T',
        'Ctrl+N',
        'Ctrl+W',
        'Ctrl+L',
        'Ctrl+P',
        'Ctrl+F',
        'Ctrl+S',
        'Ctrl+H',
        'Ctrl+J',
        'Ctrl+D',
        'Ctrl+K',
        'Alt+D',
        'Alt+F4',
        'Alt+Left',
        'Alt+Right',
    ];

    /**
     * @return list<array{keys: list<string>, label: string}>
     */
    public static function shortcutsFor(string $context): array
    {
        $search = [
            ['keys' => ['F2'], 'label' => 'Ir al buscador de productos'],
            ['keys' => ['/'], 'label' => 'Ir al buscador (si no estás escribiendo)'],
        ];

        $confirm = [
            ['keys' => ['Enter'], 'label' => 'Agregar, cobrar o confirmar según el momento'],
            ['keys' => ['Esc'], 'label' => 'Cerrar la ventana o volver'],
            ['keys' => ['?'], 'label' => 'Ver estos atajos'],
            ['keys' => ['Alt', 'Shift', 'H'], 'label' => 'Ver estos atajos (también mientras escribís)'],
        ];

        if ($context === 'purchase') {
            return array_merge($search, [
                ['keys' => ['F4'], 'label' => 'Ir al proveedor'],
                ['keys' => ['Shift', 'F4'], 'label' => 'Lista de proveedores'],
                ['keys' => ['F8'], 'label' => 'Registrar o confirmar la compra'],
            ], $confirm);
        }

        return array_merge($search, [
            ['keys' => ['F4'], 'label' => 'Ir al cliente'],
            ['keys' => ['Shift', 'F4'], 'label' => 'Lista de clientes'],
            ['keys' => ['F8'], 'label' => 'Cobrar o confirmar el cobro'],
            ['keys' => ['F9'], 'label' => 'Venta a crédito'],
        ], $confirm);
    }

    /**
     * Flattened key labels used by a context, for tests and collision checks.
     *
     * @return list<string>
     */
    public static function boundChords(string $context): array
    {
        $chords = [];

        foreach (self::shortcutsFor($context) as $row) {
            $chords[] = implode('+', $row['keys']);
        }

        return $chords;
    }
}
