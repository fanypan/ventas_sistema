# Agente — AranduTech Ventas

SaaS sales-assisted de POS para Paraguay (Laravel 13, una PostgreSQL por cliente, subdominio).

## Antes de codear

1. ¿Plataforma (`PLATFORM_PATH` / subdominio `PLATFORM_DOMAIN`) o comercio (`{slug}.dominio`)?
2. Si es comercio: rutas tenant + migraciones tenant. Nunca tablas de ventas en la DB central.
3. Skills del repo (leelas si el trabajo calza): `saas-tenancy`, `onboard-cliente`, `modulo-negocio`, `facturacion-electronica`.
4. Operación: `docs/SAAS.md`. Producto: `PRODUCT.md`. Visual: `DESIGN.md` (tokens en el frontmatter; sidecar `.impeccable/design.json`).
5. UI: no rearmar menús del POS. Índigo en producto (POS + plataforma); teal solo en landing/WhatsApp. Outfit, densidad de escritorio, voz rioplatense.
6. Este POS **no se conecta a SIFEN**. FE = HTTP a `api_facturacion_electronica` (`/api/v1`), configurada en el panel admin del comercio.
7. PHP: controller delgado + FormRequest + Action/Service. Eloquent en la Action/Service (CRUD chico puede crear desde `validated()`). Filtros repetidos: scopes del modelo con `#[Scope]` (el método es `active`, no `scopeActive`). No repositorios genéricos ni dominio hexagonal. Regla: `.cursor/rules/laravel-code.mdc`.

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

## Tags (git)

SemVer anotado: `vMAJOR.MINOR.PATCH` (ej. `v1.2.0`). Un tag = un release, no cada commit ni cada PR.

- `feat` → MINOR, `fix` → PATCH, cambio que rompe contrato → MAJOR.
- Solo cuando el usuario pide tag/release: `git tag -a vX.Y.Z -m "…"`.
- No mover ni pisar un tag ya publicado. No taguear en un commit de docs/chore suelto.
