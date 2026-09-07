<?php

return [
    /*
    | Prefijo URL del panel staff (sin barras). Cambiar en producción.
    | Ej.: PLATFORM_PATH=a7k9m2p4 → /a7k9m2p4/login
    */
    'platform_path' => preg_match('/^[a-z0-9_-]+$/i', trim((string) env('PLATFORM_PATH', 'plataforma'), '/'))
        ? trim((string) env('PLATFORM_PATH', 'plataforma'), '/')
        : 'plataforma',

    /*
    | Subdominio exclusivo del staff (opcional). Si está definido, el panel solo
    | responde en ese host; en el resto de dominios centrales devuelve 404.
    | Ej.: PLATFORM_DOMAIN=admin.tudominio.com
    */
    'platform_domain' => env('PLATFORM_DOMAIN') ?: null,

    /*
    | Contraseña del usuario staff al sembrar (PlatformUserSeeder).
    | Vacío en local/testing → "plataforma". En production hay que definirla.
    */
    'platform_admin_password' => trim((string) env('PLATFORM_ADMIN_PASSWORD', '')),

    'tenant_base_domain' => env('TENANT_BASE_DOMAIN', 'localhost'),
    'whatsapp' => env('SAAS_WHATSAPP', '595981000000'),
    'brand' => env('SAAS_BRAND', 'AranduTech Ventas'),
    'grace_days' => (int) env('SAAS_GRACE_DAYS', 7),
    'readonly_days' => (int) env('SAAS_READONLY_DAYS', 3),
    'reminder_days_before' => (int) env('SAAS_REMINDER_DAYS', 3),
    'admin_invite_hours' => (int) env('SAAS_ADMIN_INVITE_HOURS', 48),
    'sifen_driver' => env('SIFEN_DRIVER', 'null'),
    'sifen_partner_url' => env('SIFEN_PARTNER_URL'),
    'sifen_partner_token' => env('SIFEN_PARTNER_TOKEN'),
];
