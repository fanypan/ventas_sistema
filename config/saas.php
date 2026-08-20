<?php

return [
    'tenant_base_domain' => env('TENANT_BASE_DOMAIN', 'localhost'),
    'whatsapp' => env('SAAS_WHATSAPP', '595981000000'),
    'brand' => env('SAAS_BRAND', 'AranduTech Ventas'),
    'grace_days' => (int) env('SAAS_GRACE_DAYS', 7),
    'readonly_days' => (int) env('SAAS_READONLY_DAYS', 3),
    'reminder_days_before' => (int) env('SAAS_REMINDER_DAYS', 3),
    'sifen_driver' => env('SIFEN_DRIVER', 'null'),
    'sifen_partner_url' => env('SIFEN_PARTNER_URL'),
    'sifen_partner_token' => env('SIFEN_PARTNER_TOKEN'),
];
