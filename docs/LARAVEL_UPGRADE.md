# Upgrade Laravel 9 → 10 → 11

El SaaS arranca en **Laravel 9** + `stancl/tenancy` v3 a propósito: mezclar el upgrade con el tenancy duplica el riesgo. Cuando haya clientes de pago, hacer el upgrade en un PR aparte.

## Orden

1. Tests verdes (`php artisan test`).
2. Laravel 9 → 10 (PHP 8.1+ ya está en 8.2).
3. Tests otra vez.
4. Laravel 10 → 11.
5. Subir `nwidart/laravel-modules` a la major que corresponda.

## Laravel 10 (resumen)

```bash
composer require laravel/framework:^10.0 laravel/sanctum:^3.2 laravel/ui:^4.0 nwidart/laravel-modules:^10.0 --with-all-dependencies
composer require nunomaduro/collision:^7.0 phpunit/phpunit:^10.0 --dev --with-all-dependencies
```

Cambios típicos a revisar:

- `RouteServiceProvider` ya no declara `$namespace` (este repo ya usa FQCN en `routes/`).
- `lang/` en la raíz (ya está).
- Deprecations de `registerPolicies` y de `Middleware` `$routeMiddleware` → `$middlewareAliases` en L11.
- `spatie/laravel-permission` v5 sigue andando en L10; v6 para L11.
Bloqueos actuales en este repo (composer why-not): `laravel/sanctum` v2, `laravel/ui` v3, `realrashid/sweet-alert` v5. Subir esos tres junto con `laravel/framework:^10`.

## Laravel 11

Usar la guía oficial y el [upgrade de nwidart](https://nwidart.com/laravel-modules). Regenerar `bootstrap/app.php` (L11 ya no usa `Kernel` HTTP clásico) es el tramo más grande: conviene un PR solo para eso.
