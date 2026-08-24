---
name: modulo-negocio
description: >-
  Adds or changes nwidart Laravel modules (Sales, Products, Financials, Credits,
  etc.) in a tenant-safe way. Use when creating a module, adding POS/inventory
  routes, permissions, or AdminLTE views for the comercio app.
---

# Módulo de negocio

## Alta de módulo

```bash
php artisan module:build
# nombre en plural: e.g. warranties
```

El stub (`stubs/module-generator`) ya sale con `TenantMiddleware::web()` y **sin** `loadMigrationsFrom`. Verificá:

1. `Modules/{Name}/Providers/RouteServiceProvider.php` usa `TenantMiddleware::web()`.
2. El ServiceProvider del módulo **no** llama `loadMigrationsFrom(...)`.
3. Migraciones en `Modules/{Name}/Database/Migrations` (stancl las corre por tenant).
4. `module.json`: `menus` + `permissions` en **singular**. Recargar permisos o incluirlos en `BusinessPermissionSeeder`.
5. Roles: `admin` ve el CRUD; `operator` solo lo que va a caja. No crear `superadmin` de tenant.

Si usás `module:make` (nwidart), los stubs están en `stubs/nwidart-stubs` (`config/modules.php`).

## POS

Cerrar venta = `SalesAjaxController@processSale`. Stock se descuenta al carrito. Factura electrónica = HTTP a la API de facturación **después** del `DB::commit`. Este POS no habla con SIFEN.

## UI

Leé [DESIGN.md](../../../DESIGN.md) antes de tocar vistas. No rearmar menús. Textos nuevos en rioplatense. Acciones de caja en un paso (índigo, Outfit, `white-space: nowrap`). No usar el teal de la landing en el POS.
