---
name: saas-tenancy
description: >-
  Guides multi-tenant work on this Laravel SaaS (stancl/tenancy v3, one MySQL per
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
5. Nunca `loadMigrationsFrom` en el ServiceProvider del módulo.

## Alta de tenant

`Tenant::create([...])` ya dispara CreateDatabase + MigrateDatabase. `setup_password` va en la columna virtual `data`. `SetupTenantJob` siembra roles `admin`/`operator`, crea el usuario y manda el mail.

El usuario MySQL necesita `CREATE DATABASE`. Ver [docs/SAAS.md](../../docs/SAAS.md).

## Debug rápido

| Síntoma | Causa habitual |
|---|---|
| 404 en `demo.localhost/login` | Host no está en `domains` o test pegó a `localhost` |
| Landing 404 | Otra ruta `GET /` de tenant pisa la central |
| Tablas de ventas en DB `admin_lte3` | Migración central o `loadMigrationsFrom` |
| Permisos cruzados entre clientes | Cache Spatie sin forget al inicializar tenancy |
| Job ve datos del tenant anterior | Falta `$tenant->run()` / Queue bootstrapper |

Tests: extender `Tests\TenantTestCase`, HTTP a `http://demo.localhost/...`.
