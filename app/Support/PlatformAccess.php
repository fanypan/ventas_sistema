<?php

namespace App\Support;

final class PlatformAccess
{
    public const GUARD = 'platform';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_STAFF = 'staff';

    public const ROLE_BILLING = 'billing';

    /**
     * @return array<string, string>
     */
    public static function permissions(): array
    {
        return [
            'tenants.view' => 'Ver clientes',
            'tenants.create' => 'Dar de alta clientes',
            'tenants.update' => 'Editar clientes (logo, suspender, reactivar)',
            'tenants.catalog' => 'Copiar catálogo entre comercios',
            'tenants.cancel' => 'Dar de baja (sin borrar la base)',
            'tenants.delete' => 'Eliminar cliente y su base',
            'payments.create' => 'Registrar pagos',
            'plans.view' => 'Ver planes',
            'plans.update' => 'Editar planes',
            'users.view' => 'Ver el equipo',
            'users.create' => 'Crear usuarios de plataforma',
            'users.update' => 'Editar usuarios de plataforma',
            'users.delete' => 'Eliminar usuarios de plataforma',
            'roles.view' => 'Ver roles',
            'roles.create' => 'Crear roles',
            'roles.update' => 'Editar permisos de un rol',
            'roles.delete' => 'Eliminar roles',
        ];
    }

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return array_keys(self::permissions());
    }

    /**
     * @return list<string>
     */
    public static function staffPermissions(): array
    {
        return [
            'tenants.view',
            'tenants.create',
            'tenants.update',
            'tenants.catalog',
            'payments.create',
            'plans.view',
        ];
    }

    /**
     * @return list<string>
     */
    public static function billingPermissions(): array
    {
        return [
            'tenants.view',
            'payments.create',
        ];
    }

    public static function isProtectedRole(string $name): bool
    {
        return strtolower(trim($name)) === self::ROLE_ADMIN;
    }
}
