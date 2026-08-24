# Módulo `{Module}`

CRUD generado para el **POS del comercio** (DB del tenant, no `ventas_central`).

## Después de `php artisan module:build`

El stub ya incluye `TenantMiddleware` y **no** llama `loadMigrationsFrom`. Completá esto:

1. `module.json`: `permissions` en **singular** (`{model}`, no `{module}`).
2. Recargá permisos en el panel del comercio, o incluí el recurso en `BusinessPermissionSeeder`.
3. Roles: `admin` ve el CRUD; `operator` solo lo que va a caja. No crear `superadmin` de tenant.
4. Migraciones en `Database/Migrations`: las corre stancl/tenancy al aprovisionar (y `tenants:migrate` en tenants existentes).
5. UI: [DESIGN.md](../../../DESIGN.md). No rearmar menús. Textos en rioplatense.

Detalle: `.cursor/skills/modulo-negocio/SKILL.md`.
