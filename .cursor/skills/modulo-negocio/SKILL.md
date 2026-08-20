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

Luego, obligatorio:

1. En `Modules/{Name}/Providers/RouteServiceProvider.php` usar `TenantMiddleware::web()`.
2. En el ServiceProvider del módulo: **borrar** `loadMigrationsFrom(...)`.
3. Migraciones quedan en `Modules/{Name}/Database/Migrations` (stancl las corre por tenant).
4. `module.json`: `menus` + `permissions`. Recargar permisos o incluirlos en `BusinessPermissionSeeder`.
5. Roles: `admin` ve el CRUD; `operator` solo lo que va a caja. No crear `superadmin` de tenant.

## POS

Cerrar venta = `SalesAjaxController@processSale`. Stock se descuenta al carrito. Factura electrónica = HTTP a la API de facturación **después** del `DB::commit`. Este POS no habla con SIFEN.

## UI

No rearmar menús. Textos nuevos en rioplatense. Acciones de caja en un paso.
