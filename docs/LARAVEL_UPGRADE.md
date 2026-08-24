# Laravel 13 (estado actual)

El POS corre en **Laravel 13** + PHP **8.3** + `stancl/tenancy` **^3.10**. El salto desde Laravel 9 se hizo **un major por PR**, sin features de negocio ni cambios de tenancy.

No mezclar un bump de framework con features de POS. Auth del comercio se queda en `laravel/ui`.

## Stack

| Pieza | Versión |
|---|---|
| PHP | ^8.3 (Docker `php:8.3-cli-bookworm` / `php:8.3-fpm-bookworm`) |
| laravel/framework | ^13 |
| nwidart/laravel-modules | ^13 |
| spatie/laravel-permission | ^7 |
| stancl/tenancy | ^3.10 |
| laravel/ui | ^4.6.3 |
| phpunit | ^12 |

## Hops que ya se hicieron

1. **L9 → L10:** `$routeMiddleware` → `$middlewareAliases`, PHPUnit 10, sanctum 3, ui 4, nwidart 10, sweet-alert 7. Spatie se quedó en v5.
2. **L10 → L11:** `bootstrap/app.php` (`Application::configure`). Kernels HTTP/Console y `Handler` salieron. Rate limiters en `AppServiceProvider`. Schedule en `withSchedule`. Spatie v6 (`Middleware` singular). nwidart 11 + merge-plugin. **No** declarar `health: '/up'`: el check custom es [`HealthController`](../app/Http/Controllers/HealthController.php).
3. **L11 → L12:** Carbon 3 (sacar el pin `nesbot/carbon: ^2.66`), nwidart 12, PHPUnit 11. nwidart v12 auto-descubre migraciones de módulos: hay que dejar `'auto-discover.migrations' => false` en [`config/modules.php`](../config/modules.php) para que `php artisan migrate` no cree tablas de POS en la DB central.
4. **PHP 8.3** (sigue en L12): Docker, CI y `composer.json` / `platform.php`.
5. **L12 → L13:** Spatie v7 (`clearClassPermissions()` → `clearPermissionsCollection()`), nwidart 13, PHPUnit 12, CSRF `PreventRequestForgery`, `cache.serializable_classes` = `false`.

Guías oficiales: [10](https://laravel.com/docs/10.x/upgrade), [11](https://laravel.com/docs/11.x/upgrade), [12](https://laravel.com/docs/12.x/upgrade), [13](https://laravel.com/docs/13.x/upgrade).

## Convenciones que hay que conservar

- `GET /up` es el health custom (DB central + Redis + disco, `HEALTH_ENABLED`). No usar el health default de L11+.
- CSRF: middleware de app [`PreventRequestForgery`](../app/Http/Middleware/PreventRequestForgery.php). Tests: `withoutMiddleware(PreventRequestForgery::class)`.
- Prefijos de cache/sesión en config siguen el estilo L12 (`*_cache_`, `*_session`) para no invalidar Redis ni cookies.
- Session `serialization` se deja en `php` (default histórico) para no echar sesiones activas.
- Autoload `"Modules\\": "Modules/"` se mantiene; carpeta **no** migrar a `modules/`.
- `dcblogdev/laravel-module-generator` se sacó en L11 (solo llegaba a L9). El stub local `stubs/module-generator` es lo que importa.
