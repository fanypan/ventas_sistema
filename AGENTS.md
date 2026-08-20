# Agente — AranduTech Ventas

SaaS sales-assisted de POS para Paraguay (Laravel 9, una MySQL por cliente, subdominio).

## Antes de codear

1. ¿Plataforma (`/plataforma`) o comercio (`{slug}.dominio`)?
2. Si es comercio: rutas tenant + migraciones tenant. Nunca tablas de ventas en la DB central.
3. Skills del repo (leelas si el trabajo calza): `saas-tenancy`, `onboard-cliente`, `modulo-negocio`, `facturacion-electronica`.
4. Operación: `docs/SAAS.md`. Producto: `PRODUCT.md`.
5. Este POS **no se conecta a SIFEN**. FE = HTTP a `api_facturacion_electronica` (`/api/v1`), configurada en el panel admin del comercio.

## Guardarraíles

Los hooks en `.cursor/hooks.json` bloquean `migrate:fresh`, `db:wipe`, `down -v`, force-push y borrar `_archive`. Las reglas en `.cursor/rules/` se aplican solas.

## Tests

`php artisan test`. Tenants: `http://demo.localhost`, no `localhost`.

## Commits

[Conventional Commits](https://www.conventionalcommits.org/): `tipo(alcance): descripción` en imperativo, minúsculas, sin punto final.

| Tipo | Uso |
|------|-----|
| `feat` | funcionalidad nueva |
| `fix` | corrección de bug |
| `refactor` | cambio interno sin cambiar comportamiento |
| `test` | tests |
| `docs` | documentación |
| `chore` | tooling, deps, CI, agent harness |

Alcances habituales: `saas`, `platform`, `tenancy`, `billing`, `fe`, `modules`, `docker`, `agent`, `archive`, `db`.

Ejemplos: `feat(platform): add manual payment registration`, `refactor(db): move POS migrations to tenant folder`.
