# SaaS multi-tenant

Una app Laravel, **una base MySQL por comercio**, acceso por subdominio.

## Dominios

- `tudominio.com` — landing y planes
- `tudominio.com/plataforma` — staff AranduTech (alta de clientes, pagos, suspensión)
- `cliente.tudominio.com` — POS del comercio

En local, `*.localhost` resuelve solo:

- http://localhost:8090 — landing
- http://localhost:8090/plataforma/login — staff (`plataforma@arandutech.com` / `plataforma`)
- http://demo.localhost:8090/login — tenant de prueba (después del alta)

Variables en `.env`:

```
CENTRAL_DOMAINS=localhost,127.0.0.1
TENANT_BASE_DOMAIN=localhost
SAAS_WHATSAPP=595981000000
QUEUE_CONNECTION=redis
SIFEN_DRIVER=null
```

Producción: `CENTRAL_DOMAINS=tudominio.com,www.tudominio.com,admin.tudominio.com` y `TENANT_BASE_DOMAIN=tudominio.com`. DNS wildcard `*.tudominio.com` + TLS wildcard delante de Nginx.

## Alta de un cliente

1. Cierre por WhatsApp y cobro (transferencia o efectivo).
2. Staff → Plataforma → Nuevo cliente (slug, plan, mail del admin).
3. El job crea `tenant{uuid}`, corre migraciones, semilla roles `admin`/`operator`, manda credenciales.
4. Registrar el pago en la ficha del cliente para renovar el período.

Estados: activo → gracia 7 días → solo lectura 3 días → suspendido. Cron: `subscriptions:tick`.

## MySQL y tenants

El usuario de MySQL tiene que poder `CREATE DATABASE`. En Docker nuevo, `docker/mysql/init/01-tenant-grants.sql` lo hace. Si el volumen ya existía:

```sql
GRANT ALL PRIVILEGES ON *.* TO 'ventas'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
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

## Agente (Cursor)

Mapa para el agente: `AGENTS.md`. Reglas en `.cursor/rules/`, skills en `.cursor/skills/`, hooks en `.cursor/hooks.json` (bloquean `migrate:fresh`, `db:wipe`, `compose down -v` y force-push).
