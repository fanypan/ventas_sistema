# AranduTech Ventas

POS/ERP SaaS para comercios en Paraguay (Laravel 9, AdminLTE). **Una PostgreSQL por cliente**, acceso por subdominio, onboarding sales-assisted (sin signup público).

- Operación y dominios: [docs/SAAS.md](docs/SAAS.md)
- Producto: [PRODUCT.md](PRODUCT.md)
- Visual: [DESIGN.md](DESIGN.md) (índigo en POS y plataforma; teal solo en landing)

## Requisitos

**Con Docker (recomendado):** [Docker Engine](https://docs.docker.com/engine/install/) + plugin Compose v2, o [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows / Linux).

**Sin Docker:** PHP 8.2, Composer, PostgreSQL 16, Redis, extensiones `pdo_pgsql`, `gd`, `zip`, `bcmath`, `intl`, `exif`. Redis es **requerido** para colas (`QUEUE_CONNECTION=redis`): el alta de un comercio corre en background.

## Instalación

```bash
git clone https://github.com/fanypan/ventas_sistema.git
cd ventas_sistema
```

En Windows (PowerShell o Git Bash) el `cd` es el mismo. Si clonás con Git GUI, abrí la carpeta del repo en la terminal.

---

### Linux — Docker (desarrollo)

1. Copiá el entorno y ajustá el uid del usuario (evita que `vendor` y `storage` queden de root):

```bash
cp .env.example .env
```

En `.env` dejá `DB_HOST=postgres` y el uid del host:

```bash
# en .env
WWWUSER=1000
WWWGROUP=1000
```

(`id -u` e `id -g` si no sos uid 1000.)

2. Levantá el stack:

```bash
docker compose up -d --build
```

3. La app queda en **http://localhost:8090**  
   Mailpit (mails de prueba): **http://localhost:8025**  
   Postgres desde el host: `127.0.0.1:5433` (usuario/clave `ventas` / `ventas`, base `ventas_central`).

La primera vez el entrypoint corre `composer install`, migraciones y seed.

El servicio `queue` tiene que estar `Up`: sin él no se termina de aprovisionar un comercio.

---

### Windows — Docker (desarrollo)

1. Instalá [Docker Desktop](https://www.docker.com/products/docker-desktop/) con el backend **WSL 2**.
2. En PowerShell, dentro del repo:

```powershell
copy .env.example .env
docker compose up -d --build
```

En Git Bash / WSL:

```bash
cp .env.example .env
docker compose up -d --build
```

3. Abrí **http://localhost:8090**

Dejá `DB_HOST=postgres` en `.env` (es el nombre del servicio, no `127.0.0.1`).  
`WWWUSER` / `WWWGROUP` en `1000` está bien con Docker Desktop + WSL 2.

Si `docker compose` no existe, usá `docker-compose` (guión) o actualizá Docker Desktop.

---

### Linux — sin Docker

```bash
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-pgsql php8.2-gd php8.2-zip php8.2-bcmath php8.2-intl php8.2-xml php8.2-mbstring php8.2-curl php8.2-redis unzip postgresql postgresql-client redis-server
# Composer: https://getcomposer.org/download/
```

Creá la base `ventas_central` en PostgreSQL (el usuario necesita `CREATEDB` para el alta de comercios). Luego:

```bash
cp .env.example .env
```

En `.env` (valores típicos en el host):

```env
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ventas_central
DB_USERNAME=ventas
DB_PASSWORD=
REDIS_HOST=127.0.0.1
QUEUE_CONNECTION=redis
MAIL_MAILER=log
CENTRAL_DOMAINS=localhost,127.0.0.1
TENANT_BASE_DOMAIN=localhost
PLATFORM_PATH=plataforma
```

```bash
composer install
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
php artisan serve
```

En otra terminal, el worker de cola:

```bash
php artisan queue:work redis --sleep=1 --tries=3
```

App: **http://127.0.0.1:8000**

---

### Windows — sin Docker

Opciones habituales: [Laragon](https://laragon.org/) (PHP + PostgreSQL + Composer) o XAMPP + PostgreSQL + [Composer](https://getcomposer.org/Composer-Setup.exe). Instalá Redis (o usá Docker solo para Redis).

En PowerShell, con PHP y Composer en el `PATH`:

```powershell
copy .env.example .env
```

Editá `.env` igual que en Linux sin Docker (`DB_CONNECTION=pgsql`, `DB_HOST=127.0.0.1`, `REDIS_HOST=127.0.0.1`).

```powershell
composer install
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
php artisan serve
```

En otra ventana: `php artisan queue:work redis`.

Si `php` no se reconoce, usá la ruta de Laragon/XAMPP, por ejemplo:

```powershell
C:\laragon\bin\php\php-8.2.0-Win32-vs16-x64\php.exe artisan serve
```

---

### Cómo entrar (después del seed)

| Superficie | URL (Docker local) | Usuario | Contraseña |
|---|---|---|---|
| Landing | http://localhost:8090 | — | — |
| **Staff** (plataforma) | http://localhost:8090/plataforma/login | `plataforma@arandutech.com` | `plataforma` |
| POS de un comercio | http://{slug}.localhost:8090 | el mail del alta | se muestra una vez y va por mail |

Los usuarios `superadmin@` / `admin@` / `operator@` **ya no se siembran** en la base central. Cada comercio nace con un `admin` (el mail del alta) y el rol `operator` listo para asignar.

reCAPTCHA es opcional en local. Si lo usás: [consola reCAPTCHA v2](https://www.google.com/recaptcha/admin) y las claves `RECAPTCHA_SITE_KEY` / `RECAPTCHA_SECRET_KEY` en `.env`.

`*.localhost` resuelve solo. El tenant de prueba aparece después de dar de alta un cliente en el panel staff.

### Observabilidad y calidad

Apagado por default. Variables en `.env` (detalle: [docs/SAAS.md](docs/SAAS.md)):

```bash
# Errores (vacío = off)
SENTRY_LARAVEL_DSN=
# Colas con dashboard staff
HORIZON_ENABLED=false
# Debug local (solo APP_ENV=local)
TELESCOPE_ENABLED=false
```

```bash
docker compose exec app php artisan test
docker compose exec app vendor/bin/pint --test
docker compose exec app vendor/bin/phpstan analyse
docker compose --profile obs up -d   # Dozzle + Uptime Kuma
```

Healthcheck de la app: `GET /up`.

---

## Puesta en producción

`docker-compose.yml` es **solo desarrollo** (`php artisan serve`). En producción usá **`docker-compose.prod.yml`**: Nginx + PHP-FPM 8.2 + PostgreSQL 16 + Redis + **worker de cola** + **scheduler**. El código va **dentro de la imagen** (no se monta el disco). Postgres y Redis **no** se publican al host.

Proyecto Compose aparte (`name: ventas_sistema_prod`): no pisa volúmenes ni red del stack local.

### Qué tenés que tener

- Un VPS **Linux** (Ubuntu 22.04/24.04) con Docker Engine + plugin Compose v2.
- Dominio + DNS wildcard `*.tudominio.com` apuntando a la IP del servidor (el POS es `{slug}.tudominio.com`).
- Un `.env` **de producción** en el servidor: no copies el `.env` de tu notebook con `APP_DEBUG=true` y claves `ventas/ventas`.

Este compose sirve **HTTP en el puerto 80**. En internet poné un reverse proxy con certificado (Caddy, Nginx, Traefik o el panel del VPS) delante. Laravel ya confía en el proxy (`TrustProxies` con `*`).

### 1. Clonar en el servidor (Linux)

```bash
sudo apt update && sudo apt install -y git ca-certificates curl
# Docker Engine: https://docs.docker.com/engine/install/ubuntu/
git clone https://github.com/fanypan/ventas_sistema.git
cd ventas_sistema
cp .env.example .env
```

### 2. Configurar `.env`

Generá `APP_KEY` **antes** de levantar. El entrypoint de producción **no arranca** si la key está vacía.

```bash
php artisan key:generate --show
```

Si todavía no hay `vendor`, desde Docker:

```bash
docker run --rm php:8.2-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)), PHP_EOL;"
```

Pegá el resultado en `APP_KEY`. Variables importantes:

```env
APP_NAME="AranduTech Ventas"
APP_URL=https://tudominio.com
APP_KEY=base64:...
APP_PUBLISH_PORT=80

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=ventas_central
DB_USERNAME=ventas
DB_PASSWORD=una-clave-larga
REDIS_PASSWORD=otra-clave-larga
SESSION_SECURE_COOKIE=true

QUEUE_CONNECTION=redis

# Dominios SaaS (sin esto el POS por subdominio no arranca)
CENTRAL_DOMAINS=tudominio.com,www.tudominio.com,admin.tudominio.com
TENANT_BASE_DOMAIN=tudominio.com
PLATFORM_PATH=a7k9m2p4
PLATFORM_DOMAIN=admin.tudominio.com

RUN_MIGRATIONS=true
RUN_SEED=true

# El alta de cliente manda la clave por mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.tudominio.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tudominio.com
```

`docker-compose.prod.yml` **fuerza** `APP_ENV=production`, `APP_DEBUG=false`, cache/sesión/cola en Redis, `SESSION_SECURE_COOKIE=true`, `TRUSTED_PROXIES=private` y `DB_HOST=postgres`. No hay fallback `ventas` para `DB_PASSWORD`; Redis exige `REDIS_PASSWORD` (`requirepass`).

**Importante:** `POSTGRES_DB` / `POSTGRES_USER` / `POSTGRES_PASSWORD` solo se aplican la **primera** vez que se crea el volumen de Postgres. En un servidor nuevo, poné las claves definitivas **antes** del primer `up`.

`PLATFORM_PATH=plataforma` es solo para local. En internet usá un prefijo opaco: el staff entra por `https://admin.tudominio.com/{PLATFORM_PATH}/login` (bookmark interno; no hay link en la landing).

### 3. Primer arranque

```bash
docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml ps
docker compose -f docker-compose.prod.yml logs -f php nginx queue scheduler
```

Cuando `php` esté healthy, Nginx responde en `http://SERVIDOR/` (puerto `APP_PUBLISH_PORT`, por defecto 80). Confirmá que `queue` y `scheduler` estén `Up`:

- `queue` — alta de cliente y jobs de tenancy
- `scheduler` — `subscriptions:tick` (07:00) y `tenants:backup` (02:30)

Si `RUN_SEED=true` en ese primer arranque, definí `PLATFORM_ADMIN_PASSWORD` (en production el seeder no arranca sin eso). En local, si está vacío, queda `plataforma@arandutech.com` / `plataforma`. En el próximo reciclo poné `RUN_SEED=false`.

Si no sembraste, creá el usuario staff a mano o corré el seeder una sola vez:

```bash
docker compose -f docker-compose.prod.yml exec php php artisan db:seed --force
```

Firewall: abrí **80** y **443**. No abras 5432 ni 6379.

El usuario de Postgres tiene que poder `CREATE DATABASE` (en este compose ya es superuser). Si el volumen era viejo:

```sql
ALTER ROLE ventas CREATEDB;
```

### 4. HTTPS y DNS

Ejemplo mínimo con Caddy en el host (TLS wildcard; el compose sigue en el puerto 80):

```caddy
tudominio.com, www.tudominio.com, admin.tudominio.com, *.tudominio.com {
    reverse_proxy 127.0.0.1:80
}
```

`APP_URL` tiene que ser exactamente la URL pública (`https://tudominio.com`).

| Host | Qué es |
|---|---|
| `tudominio.com` | landing y planes |
| `admin.tudominio.com/{PLATFORM_PATH}` | panel staff |
| `{slug}.tudominio.com` | POS del comercio |

Opcional: restringir el panel staff por IP (`docker/nginx/platform-staff.conf.example`).

Si el proxy usa otro puerto publicado (`APP_PUBLISH_PORT=8080`), el `reverse_proxy` apunta a `127.0.0.1:8080`.

### 5. Actualizar la app

Los datos (Postgres, Redis, `storage` con logos y uploads) viven en volúmenes Docker y **no se borran** al rebuild.

```bash
cd ventas_sistema
git pull
docker compose -f docker-compose.prod.yml up -d --build
```

El entrypoint vuelve a correr migraciones (`RUN_MIGRATIONS=true`) y regenera `config` / `route` / `view` cache.

### 6. Operación diaria

```bash
docker compose -f docker-compose.prod.yml ps

docker compose -f docker-compose.prod.yml logs -f php nginx queue scheduler postgres

docker compose -f docker-compose.prod.yml exec php php artisan about
docker compose -f docker-compose.prod.yml exec php php artisan permission:cache-reset

# central.sql + un dump por tenant → storage/app/backups/{fecha}/
docker compose -f docker-compose.prod.yml exec php php artisan tenants:backup
```

Un `pg_dump` solo de `ventas_central` **no** alcanza: cada comercio es otra base (`tenant_demo`, etc.).

Bases huérfanas (sin cliente en plataforma):

```bash
docker compose -f docker-compose.prod.yml exec php php artisan tenants:cleanup-orphans --dry-run
docker compose -f docker-compose.prod.yml exec php php artisan tenants:cleanup-orphans
```

Backup de archivos subidos: volumen `ventas_sistema_prod_storage_data` (`docker volume ls`).

Para bajar el stack **sin** borrar datos:

```bash
docker compose -f docker-compose.prod.yml down
```

`down -v` **borra** Postgres, Redis y storage. No lo uses en producción salvo que quieras resetear todo.

### 7. Checklist

- [ ] `APP_KEY` definido y **nunca** regenerado después de tener datos
- [ ] `APP_DEBUG=false` (el compose ya lo fuerza)
- [ ] Contraseña de DB distinta a `ventas`
- [ ] `CENTRAL_DOMAINS`, `TENANT_BASE_DOMAIN`, `PLATFORM_PATH` (opaco) y `PLATFORM_DOMAIN`
- [ ] DNS wildcard `*.tudominio.com` + TLS wildcard
- [ ] `queue` y `scheduler` en `Up`
- [ ] SMTP real (`MAIL_MAILER=smtp`) si vas a dar de alta clientes
- [ ] `RUN_SEED=false` después del primer deploy
- [ ] `PLATFORM_ADMIN_PASSWORD` si sembrás staff en prod (no dejes `plataforma`)
- [ ] `APP_URL` con `https://`
- [ ] Puerto 5432/6379 no publicados
- [ ] Copias de `tenants:backup` y de `storage` en otro disco/nube

## Módulos de negocio

Los CRUDs del POS viven en `Modules/` y corren **en la DB del comercio**, no en `ventas_central`.

```bash
php artisan module:build
# nombre en plural: warranties, categories, etc.
```

El stub ya sale tenant-safe (`TenantMiddleware`, sin `loadMigrationsFrom`). Después del generate:

1. `module.json`: `menus` + `permissions` en **singular** (`warranty`, no `warranties`).
2. Recargar permisos en el panel del comercio, o incluir el recurso en `BusinessPermissionSeeder`.
3. `admin` ve el CRUD; `operator` solo lo que va a caja. No crear `superadmin` de tenant.

Detalle: [`.cursor/skills/modulo-negocio/SKILL.md`](.cursor/skills/modulo-negocio/SKILL.md). No rearmar menús del POS.

```bash
php artisan module:enable {Nombre}
php artisan module:disable {Nombre}
```
