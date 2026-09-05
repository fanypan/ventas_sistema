---
name: saas-tenancy
description: >-
  Guides multi-tenant work on this Laravel SaaS (stancl/tenancy v3, one PostgreSQL per
  comercio, subdomain identification). Use when creating tenants, touching
  domains, central vs tenant migrations, routes/tenant.php, TenancyServiceProvider,
  TenantMiddleware, storage per tenant, or debugging "wrong database" / 404 on
  subdomains.
---

# SaaS tenancy

## Checklist

1. ¿Esto es de **plataforma** (staff) o de **comercio**?
2. Plataforma → `routes/web.php` + migraciones en `database/migrations/` (sin `tenant/`).
3. Comercio → `routes/tenant.php` o módulo con `TenantMiddleware::web()`.
4. DDL del POS → `database/migrations/tenant/` o `Modules/*/Database/Migrations`.
5. Nunca `loadMigrationsFrom` en el ServiceProvider del módulo. `auto-discover.migrations` en `config/modules.php` tiene que quedar en `false`.

## Alta de tenant

`Tenant::create([...])` ya dispara CreateDatabase + MigrateDatabase. El **slug** es `[a-z0-9]+` (sin `-` ni `_`): la base queda `tenant_{slug}` y el host `{slug}.{TENANT_BASE_DOMAIN}`. El job siembra roles `admin`/`operator`, crea el usuario admin **sin clave usable** (`must_change_password`) y manda un enlace firmado a `/activar` (48 h). El staff reenvía desde la ficha; no hay flash de contraseña.

Si falla create/migrate/setup, `TenantProvisioningRollback` borra la base y el registro central. Bases huérfanas: `php artisan tenants:cleanup-orphans`.

El usuario Postgres necesita `CREATEDB`. Ver [docs/SAAS.md](../../docs/SAAS.md).

## Debug rápido

| Síntoma | Causa habitual |
|---|---|
| 404 en `demo.localhost/login` | Host no está en `domains` o test pegó a `localhost` |
| Landing 404 | Otra ruta `GET /` de tenant pisa la central |
| Tablas de ventas en DB `ventas_central` | Migración central o `loadMigrationsFrom` |
| Permisos cruzados entre clientes | Cache Spatie con la misma clave; tiene que ir namespaced por tenant id |
| Staff ve de más / de menos en `/plataforma` | Roles Spatie del guard `platform` (DB central), no los del POS. Se asignan en Equipo |
| Job ve datos del tenant anterior | Falta `$tenant->run()` / Queue bootstrapper |

Tests: extender `Tests\TenantTestCase`, HTTP a `http://demo.localhost/...`.
