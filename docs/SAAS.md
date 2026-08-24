# SaaS multi-tenant

Una app Laravel, **una base PostgreSQL por comercio**, acceso por subdominio.

Look y tokens: [DESIGN.md](../DESIGN.md). Producto: [PRODUCT.md](../PRODUCT.md).

## Dominios

- `tudominio.com` — landing y planes (sin link al panel staff)
- `admin.tudominio.com/{PLATFORM_PATH}` — staff AranduTech (alta de clientes, pagos, suspensión)
- `cliente.tudominio.com` — POS del comercio

En local, `*.localhost` resuelve solo:

- http://localhost:8090 — landing
- http://localhost:8090/plataforma/login — staff en dev (`plataforma@arandutech.com` / `plataforma`; cambiar `PLATFORM_PATH` en prod)
- http://demo.localhost:8090/ — tenant de prueba (después del alta)

Variables en `.env`:

```
CENTRAL_DOMAINS=localhost,127.0.0.1
TENANT_BASE_DOMAIN=localhost
PLATFORM_PATH=plataforma
# PLATFORM_DOMAIN=admin.tudominio.com
SAAS_WHATSAPP=595981000000
QUEUE_CONNECTION=redis
SIFEN_DRIVER=null
```

Producción recomendada:

```
DB_DATABASE=ventas_central
DB_USERNAME=ventas
DB_PASSWORD=<secreto>
REDIS_PASSWORD=<secreto>
SESSION_SECURE_COOKIE=true
TRUSTED_PROXIES=private
CENTRAL_DOMAINS=tudominio.com,www.tudominio.com,admin.tudominio.com
TENANT_BASE_DOMAIN=tudominio.com
PLATFORM_PATH=a7k9m2p4
PLATFORM_DOMAIN=admin.tudominio.com
PLATFORM_ADMIN_PASSWORD=<secreto>
```

Staff entra solo por `https://admin.tudominio.com/a7k9m2p4/login` (bookmark interno; no hay link en la landing). La ruta `/plataforma` devuelve 404 si `PLATFORM_PATH` es otro valor. Login con máximo 5 intentos por minuto por IP. Si cambia la contraseña, las demás sesiones (comercio y staff) quedan fuera.

`docker-compose.prod.yml` no arranca sin `DB_PASSWORD` ni `REDIS_PASSWORD`; Redis usa `requirepass` y las cookies de sesión van con `Secure`. Nginx pisa `X-Forwarded-For` con la IP real y PHP solo confía en redes privadas (`TRUSTED_PROXIES=private`). En local, con `php artisan serve`, no se confía en esos headers.

El seeder de staff en production exige `PLATFORM_ADMIN_PASSWORD`. En local, si está vacío, queda `plataforma@arandutech.com` / `plataforma`.

Opcional: restringir por IP en Nginx (`docker/nginx/platform-staff.conf.example`). DNS wildcard `*.tudominio.com` + TLS wildcard delante de Nginx.

## Alta de un cliente

1. Cierre por WhatsApp y cobro (transferencia o efectivo).
2. Staff → Plataforma → Nuevo cliente (slug, plan, mail del admin).
3. El job crea la base `tenant_{slug}` (ej. `tenant_demo`), corre migraciones, semilla roles `admin`/`operator`, manda credenciales.
4. Si falla el aprovisionamiento, se revierte solo: se borra la base y el registro central (podés reintentar con el mismo slug).
5. Registrar el pago en la ficha del cliente para renovar el período.

Estados: activo → gracia 7 días → solo lectura 3 días → suspendido. Cron: `subscriptions:tick`.

## PostgreSQL y tenants

Convención de nombres:

| Base | Uso | Ejemplo |
|------|-----|---------|
| `ventas_central` | Plataforma (tenants, planes, pagos, staff) | `.env`: `DB_DATABASE=ventas_central` |
| `tenant_{slug}` | Un comercio | `tenant_demo`, `tenant_minegocio` |

En producción no uses nombres legacy tipo `admin_lte3`; la central siempre es `ventas_central`.

El usuario de Postgres tiene que poder `CREATE DATABASE`. En Docker, `POSTGRES_USER` es superuser del contenedor. Si el volumen ya existía con un rol sin ese privilegio:

```sql
ALTER ROLE ventas WITH CREATEDB;
```

Si quedó una base huérfana (sin cliente en plataforma), por ejemplo tras un fallo previo:

```bash
php artisan tenants:cleanup-orphans --dry-run
php artisan tenants:cleanup-orphans
```

## Backups

```bash
php artisan tenants:backup
# o
./scripts/backup-tenants.sh
```

Quedan en `storage/app/backups/{fecha}/` (central.sql + un dump por tenant).

## Facturación electrónica

Este POS **no se conecta a SIFEN**. La SET la atiende el servicio `api_facturacion_electronica`. El comercio configura en su panel admin la URL del servicio y la API key (`sk_test_` / `sk_live_`). Al cerrar la venta se hace `POST /api/v1/documents`; el cupo mensual lo marca el plan. XML, firma y certificados F1 viven en la API, no acá.

## Observabilidad

Todo se prende o apaga por `.env`. Vacío / `false` = off.

| Variable | Default | Qué hace |
|---|---|---|
| `SENTRY_LARAVEL_DSN` | vacío | Errores y performance. Sin DSN el SDK no envía nada. |
| `SENTRY_TRACES_SAMPLE_RATE` | `0` | Traces APM (0.1 en prod si querés sampling). |
| `HORIZON_ENABLED` | `false` | El contenedor `queue` corre Horizon en vez de `queue:work`. UI en `/{PLATFORM_PATH}/horizon` (solo staff). |
| `TELESCOPE_ENABLED` | `false` | Debug local. Solo con `APP_ENV=local` y paquete require-dev. UI en `/{PLATFORM_PATH}/telescope`. |
| `HEALTH_ENABLED` | `true` | `GET /up` (DB central + Redis + disco). No recorre tenants. |

Sentry taguea `surface` (tenant/central) y `tenant` / `tenant_slug`. No manda PII (`send_default_pii=false`) y filtra passwords, tokens, API keys de FE.

CI (GitHub Actions): Pint (archivos de observabilidad), PHPStan, `php artisan test`, `composer audit` (informativo).

Logs de Docker y uptime en local:

```bash
docker compose --profile obs up -d
```

- Dozzle: http://localhost:9999 (socket de Docker; solo local)
- Uptime Kuma: http://localhost:3001 — monitorá `http://app:8000/up`

En producción el healthcheck de Nginx pega a `/up`. Telescope no entra a la imagen (`composer --no-dev`).

## Agente (Cursor)

Mapa para el agente: `AGENTS.md`. Reglas en `.cursor/rules/`, skills en `.cursor/skills/`, hooks en `.cursor/hooks.json` (bloquean `migrate:fresh`, `db:wipe`, `compose down -v` y force-push).
