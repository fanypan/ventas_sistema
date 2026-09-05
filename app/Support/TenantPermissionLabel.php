<?php

namespace App\Support;

final class TenantPermissionLabel
{
    private const GROUPS = [
        'sale' => 'Ventas',
        'product' => 'Productos',
        'category' => 'Categorías',
        'brand' => 'Marcas',
        'customer' => 'Clientes',
        'purchase' => 'Compras',
        'supplier' => 'Proveedores',
        'credit' => 'Créditos',
        'cash' => 'Caja',
        'expense' => 'Gastos',
        'insumo' => 'Insumos',
        'stock' => 'Stock',
        'user' => 'Usuarios',
        'role' => 'Roles',
        'permission' => 'Permisos',
        'setting' => 'Configuración',
        'module' => 'Módulos',
        'report' => 'Reportes',
        'filemanager' => 'Archivos',
    ];

    private const ACTIONS = [
        'create' => 'Crear',
        'read' => 'Ver',
        'update' => 'Editar',
        'delete' => 'Borrar',
        'void' => 'Anular',
        'consume' => 'Consumir',
    ];

    public static function groupKey(string $name): string
    {
        $parts = explode(' ', $name, 2);

        return $parts[1] ?? $parts[0];
    }

    public static function groupLabel(string $name): string
    {
        $key = self::groupKey($name);

        return self::GROUPS[$key] ?? ucfirst($key);
    }

    public static function actionLabel(string $name): string
    {
        $parts = explode(' ', $name, 2);
        if (count($parts) === 1) {
            return self::GROUPS[$parts[0]] ?? $parts[0];
        }

        return self::ACTIONS[$parts[0]] ?? $parts[0];
    }
}
