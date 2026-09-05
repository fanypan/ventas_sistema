# AranduTech Ventas

POS/ERP SaaS para comercios en Paraguay (Laravel 13, AdminLTE). **Una PostgreSQL por cliente**, acceso por subdominio, onboarding sales-assisted (sin signup público).

- Operación y dominios: [docs/SAAS.md](docs/SAAS.md)
- Producto: [PRODUCT.md](PRODUCT.md)
- Visual: [DESIGN.md](DESIGN.md) (índigo en POS y plataforma; teal solo en landing)
- Tras instalar: [crear comercios SaaS o un solo comercio](#crear-comercios-saas-o-un-solo-comercio)

## Requisitos

**Con Docker (recomendado):** [Docker Engine](https://docs.docker.com/engine/install/) + plugin Compose v2, o [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows / Linux). El compose trae PostgreSQL 16, Redis y MinIO. Mailpit y observabilidad son perfiles opcionales.

**Sin Docker:** PHP 8.3, Composer, PostgreSQL 16, Redis, extensiones `pdo_pgsql`, `gd`, `zip`, `bcmath`, `intl`, `exif`. Redis es **requerido** para colas (`QUEUE_CONNECTION=redis`): el alta de un comercio corre en background.

## Instalación

```bash
git clone https://github.com/fanypan/ventas_sistema.git
cd ventas_sistema
```

En Windows (PowerShell o Git Bash) el `cd` es el mismo. Si clonás con Git GUI, abrí la carpeta del repo en la terminal.

### Dónde clonar

Docker monta el repo en `/var/www/html` **adentro** del contenedor. En el host no hace falta `/var/www`.

| Uso | Dónde |
|---|---|
| Desarrollo Linux | Cualquier carpeta nativa: `~/proyectos/ventas_sistema`, `~/Escritorio/Proyectos/…` |
| Desarrollo Windows | **Adentro de WSL** (`\\wsl$\Ubuntu\home\…`), no en `C:\Users\…` ni `/mnt/c/…` |
| Producción | `/opt/ventas_sistema` o `/srv/ventas_sistema`, dueño un usuario de deploy en el grupo `docker` |

Evitá USB FAT/exFAT, discos de red y (en WSL) el filesystem de Windows: Composer y Postgres se vuelven lentos y `storage` queda con permisos raros. En Linux una ruta con `Escritorio` está bien.

En producción el código entra **en la imagen**; esa carpeta del servidor es solo para `git pull`, `.env` y Compose. No dejes el clone en el escritorio de un usuario.

### Permisos

Las únicas carpetas que Laravel tiene que poder escribir son `storage/` y `bootstrap/cache/`. El resto del repo puede ser de solo lectura. No hace falta `chmod 777` ni tocar `vendor` a mano.

**Con Docker (desarrollo):** el entrypoint ya hace `chown` de `storage` y `bootstrap/cache` al uid de `WWWUSER`. Poné el uid/gid del host para que `vendor` no quede de root:

```bash
id -u   # → WWWUSER
id -g   # → WWWGROUP
```

Si un arranque viejo dejó `vendor` o `storage` de root:

```bash
sudo chown -R "$(id -u):$(id -g)" vendor storage bootstrap/cache
```

**Sin Docker** (PHP del host o php-fpm de nginx):

```bash
sudo chown -R "$USER:www-data" storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
php artisan storage:link
```

En producción el entrypoint corre como `www-data` sobre el volumen `storage_data`; no hace falta `WWWUSER`.

---

### Linux — Docker (desarrollo)

1. Copiá el entorno y ajustá el uid del usuario (evita que `vendor` y `storage` queden de root):

```bash
cp .env.example .env
```

En `.env` dejá `DB_HOST=postgres`, `QUEUE_CONNECTION=redis` y el uid del host:

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

Eso es el POS (app, cola, Postgres, Redis, MinIO). Mailpit y observabilidad no arrancan. Ver [qué servicios levantar](#qué-servicios-levantar).

3. La app queda en **http://localhost:8090**  
   MinIO (fotos y archivos): consola **http://localhost:9001** (`minioadmin` / `minioadmin`)  
   Postgres desde el host: `127.0.0.1:5433` (usuario/clave `ventas` / `ventas`, base `ventas_central`).

La primera vez el entrypoint corre `composer install`, migraciones y seed. El servicio `minio-init` crea los buckets; esperá a que termine (`docker compose ps`).

El servicio `queue` tiene que estar `Up`: sin él no se termina de aprovisionar un comercio. El worker siempre usa Redis; no dejes `QUEUE_CONNECTION=sync` en `.env`.

#### Qué servicios levantar

Los perfiles son **extras**. Un `up` a secas ya es el sistema (PC del comercio incluida).

```bash
# POS: app, cola, Postgres, Redis, MinIO
docker compose up -d --build

# + Mailpit (bandeja en http://localhost:8025). En .env: MAIL_MAILER=smtp
docker compose --profile mail up -d

# + Dozzle y Uptime Kuma (solo desarrollo)
docker compose --profile obs up -d
```

Se pueden combinar: `docker compose --profile mail --profile obs up -d`.

| Servicio | Default `up` | Perfil | Para qué |
|---|---|---|---|
| `app` + `queue` | sí | — | POS, landing, staff, alta de cliente |
| `postgres` + `redis` | sí | — | bases y colas |
| `minio` + `minio-init` | sí | — | fotos y archivos |
| `mailhog` (Mailpit) | no | `mail` | mails de prueba |
| `dozzle` + `kuma` | no | `obs` | logs y uptime de desarrollo |

Sin Mailpit, `MAIL_MAILER=log` (así viene `.env.example`): el enlace del alta queda en el log. Para verlo en bandeja: `MAIL_MAILER=smtp` y `--profile mail`.

---

### Windows — Docker (desarrollo)

1. Instalá [Docker Desktop](https://www.docker.com/products/docker-desktop/) con el backend **WSL 2**.
2. Cloná **dentro de WSL** (no en `C:\Users\…`). En PowerShell, dentro del repo:

```powershell
copy .env.example .env
docker compose up -d --build
```

En Git Bash / WSL:

```bash
cp .env.example .env
docker compose up -d --build
```

3. Abrí **http://localhost:8090** (MinIO: **http://localhost:9001**).

Dejá `DB_HOST=postgres` y `QUEUE_CONNECTION=redis` en `.env` (`postgres` es el nombre del servicio, no `127.0.0.1`).  
`WWWUSER` / `WWWGROUP` en `1000` está bien con Docker Desktop + WSL 2.

Si `docker compose` no existe, usá `docker-compose` (guión) o actualizá Docker Desktop.

Mailpit y observabilidad son opcionales: [qué servicios levantar](#qué-servicios-levantar).

---

### Linux — sin Docker

```bash
sudo apt update
sudo apt install php8.3 php8.3-cli php8.3-pgsql php8.3-gd php8.3-zip php8.3-bcmath php8.3-intl php8.3-xml php8.3-mbstring php8.3-curl php8.3-redis unzip postgresql postgresql-client redis-server
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
FILESYSTEM_DISK=local
MEDIA_PUBLIC_DISK=public
MEDIA_PRIVATE_DISK=filemanager
FILEMANAGER_DRIVER=local
QUEUE_CONNECTION=redis
MAIL_MAILER=log
CENTRAL_DOMAINS=localhost,127.0.0.1
TENANT_BASE_DOMAIN=localhost
PLATFORM_PATH=plataforma
```

Sin MinIO, las fotos van a `storage/app/public`. Si levantás MinIO aparte, usá los discos `minio` / `filemanager` como en `.env.example`.

```bash
composer install
php artisan key:generate
sudo chown -R "$USER:www-data" storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
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

Editá `.env` igual que en Linux sin Docker (`DB_CONNECTION=pgsql`, `DB_HOST=127.0.0.1`, `REDIS_HOST=127.0.0.1`, discos locales si no hay MinIO).

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
C:\laragon\bin\php\php-8.3.0-Win32-vs16-x64\php.exe artisan serve
```

---

### Cómo entrar (después del seed)

| Superficie | URL (Docker local) | Usuario | Contraseña |
|---|---|---|---|
| Landing | http://localhost:8090 | — | — |
| **Staff** (plataforma) | http://localhost:8090/plataforma/login | `plataforma@arandutech.com` | `plataforma` |
| POS de un comercio | http://{slug}.localhost:8090 | el mail del alta | se muestra una vez y va por mail |

Los usuarios `superadmin@` / `admin@` / `operator@` **ya no se siembran** en la base central. Cada comercio nace con un `admin` (el mail del alta) y el rol `operator` listo para asignar.

El POS del comercio se puede **instalar como PWA** (Chrome/Android; en iOS, Compartir → Agregar a pantalla de inicio). La landing y el panel staff no son PWA.

reCAPTCHA es opcional en local. Si lo usás: [consola reCAPTCHA v2](https://www.google.com/recaptcha/admin) y las claves `RECAPTCHA_SITE_KEY` / `RECAPTCHA_SECRET_KEY` en `.env`.

`*.localhost` resuelve solo. El tenant de prueba aparece después de dar de alta un cliente en el panel staff.

---

## Crear comercios: SaaS o un solo comercio

El stack es **el mismo** (central + cola + una PostgreSQL por comercio). Lo que cambia es cuántos tenants creás, el plan y el DNS. Detalle operativo: [docs/SAAS.md](docs/SAAS.md).

### A) SaaS — varios comercios en un servidor

Para AranduTech (o un partner) que vende suscripciones mensuales/anuales.

1. Levantá el stack (Docker local o `docker-compose.prod.yml`) con DNS **wildcard** `*.tudominio.com` y las vars de dominio:

```env
CENTRAL_DOMAINS=tudominio.com,www.tudominio.com,admin.tudominio.com
TENANT_BASE_DOMAIN=tudominio.com
PLATFORM_PATH=a7k9m2p4
PLATFORM_DOMAIN=admin.tudominio.com
```

2. Confirmá que `queue` esté `Up` (sin worker el alta queda en `pending`).
3. Entrá al panel staff → **Nuevo cliente**: nombre, RUC, slug, plan público (Starter / Negocio / …), período **mensual** o **anual**, mail del admin.
4. El job crea `tenant_{slug}`, migra el POS, siembra roles y manda el mail con el enlace de 48 h para definir contraseña.
5. En la ficha del cliente, **Registrar pago** para arrancar/renovar el período.
6. El comercio opera en `https://{slug}.tudominio.com`. Staff en `https://admin.tudominio.com/{PLATFORM_PATH}/login`.

Opcional en el alta o en la ficha: **Copiar catálogo** desde otro comercio (stock en 0).

Estados de suscripción: activo → gracia 7 d → solo lectura 3 d → suspendido (`subscriptions:tick` en el scheduler).

### B) Un solo comercio — instalación propia (on-prem)

El comercio hostea en su VPS o en la LAN. Un tenant, plan interno **Instalación propia** (`onprem`): no sale en la landing, sin tope de usuarios/cajas, **sin vencimiento**, sin FE en el cupo del plan. La licencia se cobra afuera del panel.

1. Copiá `.env` y editá `APP_URL`, `DB_*`, `REDIS_*`, `MAIL_*` y dominios (abajo).

En la **PC del comercio** el default ya es el POS (sin Mailpit ni observabilidad):

```bash
cp .env.example .env
docker compose up -d --build
```

En un **VPS** (`docker-compose.prod.yml` no trae perfiles `mail`/`obs`):

```bash
docker compose -f docker-compose.prod.yml up -d --build
```

Extras locales: [qué servicios levantar](#qué-servicios-levantar).

2. En `.env`, apuntá al host del comercio (no hace falta wildcard si hay un solo POS):

```env
APP_URL=https://pos.minegocio.com
CENTRAL_DOMAINS=pos.minegocio.com,admin.minegocio.com
TENANT_BASE_DOMAIN=minegocio.com
PLATFORM_PATH=plataforma
PLATFORM_DOMAIN=admin.minegocio.com
```

Ejemplo LAN / sin DNS público: `TENANT_BASE_DOMAIN=localhost` y `CENTRAL_DOMAINS=localhost,127.0.0.1` (igual que en desarrollo).

3. Con el seed (`RUN_SEED=true` la primera vez, o `php artisan db:seed`) queda el plan **Instalación propia**.
4. Staff → **Nuevo cliente** → plan **Instalación propia**. El período se fuerza a **Sin vencimiento**. No registres pago mensual: `subscriptions:tick` no pausa este plan.
5. El POS queda en `{slug}.{TENANT_BASE_DOMAIN}` (ej. slug `pos` → `pos.minegocio.com`). Si el comercio quiere entrar por el apex (`minegocio.com`), agregá ese host en la tabla `domains` del tenant (además del subdominio que crea el alta).
6. Cambiá la clave del staff sembrado y poné `RUN_SEED=false`.

No uses un plan “gratis” público para esto: canibaliza Starter/Negocio y aparece en la landing.

| | SaaS | Instalación propia |
|---|---|---|
| Plan | público (Starter, Negocio, …) | `onprem` (interno) |
| Período | mensual / anual | sin vencimiento |
| Pago en panel | sí (renueva) | no (licencia afuera) |
| DNS | wildcard `*.dominio` | un host o LAN alcanza |
| Cantidad de comercios | muchos | uno (puede haber más, pero el caso típico es uno) |

---

### Observabilidad y calidad

Apagado por default. En la PC del comercio no uses `--profile obs` ni `--profile mail`; un `up` a secas alcanza ([qué servicios levantar](#qué-servicios-levantar)).

Variables en `.env` (detalle: [docs/SAAS.md](docs/SAAS.md)):

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
docker compose exec app vendor/bin/phpstan analyse --memory-limit=2G
docker compose --profile obs up -d   # Dozzle + Uptime Kuma
```

Healthcheck de la app: `GET /up`.

---

## Puesta en producción

Después del arranque, creá el primer comercio según el modo: [SaaS o instalación propia](#crear-comercios-saas-o-un-solo-comercio).

`docker-compose.yml` es **solo desarrollo** (`php artisan serve`). En producción usá **`docker-compose.prod.yml`**: Nginx + PHP-FPM 8.3 + PostgreSQL 16 + Redis + MinIO + **worker de cola** + **scheduler**. El código va **dentro de la imagen** (no se monta el disco). Postgres, Redis y MinIO **no** se publican al host.

Proyecto Compose aparte (`name: ventas_sistema_prod`): no pisa volúmenes ni red del stack local.

### Qué tenés que tener

- Un VPS **Linux** (Ubuntu 22.04/24.04) con Docker Engine + plugin Compose v2. El usuario de deploy tiene que estar en el grupo `docker` (`sudo usermod -aG docker $USER` y re-login).
- El repo en `/opt/ventas_sistema` o `/srv/ventas_sistema` (no en el escritorio de un usuario).
- Dominio: en **SaaS**, DNS wildcard `*.tudominio.com` (POS = `{slug}.tudominio.com`). En **instalación propia**, alcanza un host o LAN; ver [crear comercios](#crear-comercios-saas-o-un-solo-comercio).
- Un `.env` **de producción** en el servidor: no copies el `.env` de tu notebook con `APP_DEBUG=true` y claves `ventas/ventas`.

Este compose sirve **HTTP en el puerto 80**. En internet poné un reverse proxy con certificado (Caddy, Nginx, Traefik o el panel del VPS) delante. Laravel confía solo en redes privadas (`TRUSTED_PROXIES=private`: Docker/LAN). No uses `*` en internet.

### 1. Clonar en el servidor (Linux)

```bash
sudo apt update && sudo apt install -y git ca-certificates curl
# Docker Engine: https://docs.docker.com/engine/install/ubuntu/
sudo mkdir -p /opt/ventas_sistema
sudo chown "$USER:$USER" /opt/ventas_sistema
git clone https://github.com/fanypan/ventas_sistema.git /opt/ventas_sistema
cd /opt/ventas_sistema
cp .env.example .env
```

### 2. Configurar `.env`

Generá `APP_KEY` **antes** de levantar. El entrypoint de producción **no arranca** si la key está vacía.

```bash
php artisan key:generate --show
```

Si todavía no hay `vendor`, desde Docker:

```bash
docker run --rm php:8.3-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)), PHP_EOL;"
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
DB_PASSWORD=una-clave-db
REDIS_PASSWORD=una-clave-redis
SESSION_SECURE_COOKIE=true

QUEUE_CONNECTION=redis

# Fotos y archivos (MinIO; el compose prod no arranca sin MINIO_ROOT_PASSWORD)
MINIO_ROOT_USER=minio
MINIO_ROOT_PASSWORD=una-clave-minio
MINIO_BUCKET_PUBLIC=ventas-public
MINIO_BUCKET_PRIVATE=ventas-private
AWS_ACCESS_KEY_ID=minio
AWS_SECRET_ACCESS_KEY=una-clave-minio
AWS_URL=https://tudominio.com/media/ventas-public
AWS_PUBLIC_ENDPOINT=https://tudominio.com/media
AWS_ENDPOINT=http://minio:9000
AWS_USE_PATH_STYLE_ENDPOINT=true

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

`docker-compose.prod.yml` **fuerza** `APP_ENV=production`, `APP_DEBUG=false`, cache/sesión/cola en Redis, `SESSION_SECURE_COOKIE=true`, `TRUSTED_PROXIES=private` y `DB_HOST=postgres`. No hay fallback `ventas` para `DB_PASSWORD`; Redis exige `REDIS_PASSWORD` (`requirepass`); MinIO exige `MINIO_ROOT_PASSWORD`. Nginx publica las fotos en `/media/` (por eso `AWS_URL` apunta a `https://tudominio.com/media/ventas-public`). En producción **no** se publican los puertos 9000/9001 de MinIO.

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

Firewall: abrí **80** y **443**. No abras 5432, 6379 ni 9000/9001.

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

Los datos viven en volúmenes Docker y **no se borran** al rebuild: Postgres, Redis, `storage` (logs, backups SQL, cache) y **MinIO** (fotos de producto, gestor de archivos, comprobantes de pago).

```bash
cd /opt/ventas_sistema
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

Backup de archivos: volumen `ventas_sistema_prod_storage_data` (logs/backups) **y** `ventas_sistema_prod_minio_data` (fotos y uploads). Un dump SQL no alcanza. Detalle: [docs/SAAS.md](docs/SAAS.md#minio-fotos-y-archivos).

Para bajar el stack **sin** borrar datos:

```bash
docker compose -f docker-compose.prod.yml down
```

`down -v` **borra** Postgres, Redis, storage y MinIO. No lo uses en producción salvo que quieras resetear todo.

### 7. Checklist

- [ ] `APP_KEY` definido y **nunca** regenerado después de tener datos
- [ ] `APP_DEBUG=false` (el compose ya lo fuerza)
- [ ] Contraseña de DB distinta a `ventas`
- [ ] `REDIS_PASSWORD` y `MINIO_ROOT_PASSWORD` distintos a los de local
- [ ] `AWS_URL` / `AWS_PUBLIC_ENDPOINT` con `https://tudominio.com/media/…`
- [ ] `CENTRAL_DOMAINS`, `TENANT_BASE_DOMAIN`, `PLATFORM_PATH` (opaco) y `PLATFORM_DOMAIN`
- [ ] DNS wildcard `*.tudominio.com` + TLS wildcard
- [ ] `queue` y `scheduler` en `Up`
- [ ] SMTP real (`MAIL_MAILER=smtp`) si vas a dar de alta clientes
- [ ] `RUN_SEED=false` después del primer deploy
- [ ] `PLATFORM_ADMIN_PASSWORD` si sembrás staff en prod (no dejes `plataforma`)
- [ ] `APP_URL` con `https://`
- [ ] Puerto 5432/6379/9000/9001 no publicados
- [ ] Copias de `tenants:backup`, del volumen `storage` y del volumen MinIO en otro disco/nube

## Módulos de negocio

Los CRUDs del POS viven en `Modules/` y corren **en la DB del comercio**, no en `ventas_central`. Hoy: Sales, Products, Purchases, Customers, Suppliers, Credits, Financials, StockAdjustments.

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
