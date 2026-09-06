# AranduTech Ventas

POS/ERP SaaS para comercios en Paraguay (Laravel 13, AdminLTE). **Una PostgreSQL por cliente**, acceso por subdominio, onboarding sales-assisted (sin signup público).

- Operación y dominios: [docs/SAAS.md](docs/SAAS.md)
- Producto: [PRODUCT.md](PRODUCT.md)
- Visual: [DESIGN.md](DESIGN.md) (índigo en POS y plataforma; teal solo en landing)
- Tras instalar: [crear comercios SaaS o un solo comercio](#crear-comercios-saas-o-un-solo-comercio)
- Producción Linux: [VPS o PC](#linux--producción)
- Producción Windows (PC de la caja): [Windows — producción](#windows--producción)
- Programar en local: [desarrollo](#desarrollo-local)

## Requisitos

**Docker.** En Linux: [Docker Engine](https://docs.docker.com/engine/install/) + plugin Compose v2. En Windows: [Docker Desktop](https://www.docker.com/products/docker-desktop/) con backend **WSL 2**. No instales PHP ni Composer en el host: el contenedor los trae.

Producción usa `docker-compose.prod.yml` (Nginx + PHP-FPM + Postgres + Redis + MinIO + cola + scheduler). `docker-compose.yml` es **solo** para [desarrollo](#desarrollo-local).

Camino sin Docker: [alternativa sin Docker](#alternativa-sin-docker).

## Instalación (producción)

El mismo `docker-compose.prod.yml` en Linux y en Windows. Windows **no** es solo desarrollo: la PC de la caja usa esta sección. Programar en una notebook es [desarrollo local](#desarrollo-local) (`docker-compose.yml`, puerto 8090).

- [Linux — producción](#linux--producción) (VPS o PC)
- [Windows — producción](#windows--producción) (PC de la caja, Docker Desktop + WSL 2)
- Después: [configurar `.env`](#configurar-env) · [primer arranque](#primer-arranque) · [cómo entrar](#cómo-entrar)

El entrypoint hace `storage:link`, migraciones y (si `RUN_SEED=true`) seed. **No** corrás `composer install` ni `php artisan` en el host. El código va **dentro de la imagen**; en el servidor solo hacen falta el repo (para `git pull` y Compose), `.env` y `./backups`.

`cp .env.example .env` y `docker compose` buscan archivos **en el directorio actual**. Tenés que estar adentro del clone (Linux: `/opt/ventas_sistema`; Windows/WSL: `~/ventas_sistema`). Desde el Escritorio o `C:\Users` no levanta nada.

### Dónde clonar

| Uso                            | Dónde                                                                                                      |
| ------------------------------ | ---------------------------------------------------------------------------------------------------------- |
| Prod Linux (VPS o PC)          | `/opt/ventas_sistema` (recomendado) o `/srv/ventas_sistema`. Dueño: usuario de deploy en el grupo `docker` |
| Prod Windows (PC del comercio) | WSL 2: `~/ventas_sistema` en Ubuntu (`\\wsl$\Ubuntu\home\…\ventas_sistema`). No `C:\Users\…` ni OneDrive |
| Desarrollo Linux               | Carpeta nativa: `~/proyectos/ventas_sistema`                                                               |
| Desarrollo Windows             | **Adentro de WSL** (`\\wsl$\Ubuntu\home\…`), no en `C:\Users\…` ni `/mnt/c/…`                              |

Evitá USB FAT/exFAT, discos de red y (en WSL) el filesystem de Windows. En **prod** no uses el Escritorio.

Los dumps SQL pueden vivir aparte (`BACKUP_HOST_PATH`, p.ej. Google Drive). El repo no.

### Linux — producción

1. Instalá Git y Docker. El usuario de deploy tiene que estar en el grupo `docker`:

```bash
sudo apt update && sudo apt install -y git ca-certificates curl
# Docker Engine: https://docs.docker.com/engine/install/ubuntu/
sudo usermod -aG docker "$USER"
# cerrá sesión y volvé a entrar para que el grupo aplique
```

2. Cloná en `/opt` y **entrá** a la carpeta (el `cd` no es opcional):

```bash
sudo mkdir -p /opt/ventas_sistema
sudo chown "$USER:$USER" /opt/ventas_sistema
git clone https://github.com/fanypan/ventas_sistema.git /opt/ventas_sistema
cd /opt/ventas_sistema
pwd
# tiene que ser /opt/ventas_sistema
ls docker-compose.prod.yml
# si dice "No such file", no estás en el repo
```

3. Copiá el entorno **desde esa misma carpeta**:

```bash
cp .env.example .env
```

4. Seguí en [configurar `.env`](#configurar-env) y [primer arranque](#primer-arranque). El comando de Compose es:

```bash
docker compose -f docker-compose.prod.yml up -d --build
```

No uses `docker compose up` a secas: eso levanta el compose de **desarrollo**.

### Windows — producción

**PC de la caja en el comercio**, no la notebook de desarrollo. Mismo `docker-compose.prod.yml` que Linux (Nginx, puerto 80). El código vive **en Ubuntu (WSL)**, no en `C:\Users`. Docker Desktop es el motor; los comandos se corren **adentro de Ubuntu**.

`docker-desktop` en `wsl -l -v` **no alcanza**. Esa distro es interna de Docker (`pwd` = `/mnt/host/Users/…`). Hace falta una distro **Ubuntu** aparte.

1. En **PowerShell**:

```powershell
wsl -l -v
```

Si solo aparece `docker-desktop` (Stopped), instalá Ubuntu:

```powershell
wsl --install -d Ubuntu
```

Reiniciá la PC si Windows lo pide. La primera vez que abras **Ubuntu** (menú Inicio) te pide un usuario y una contraseña: crealos (no uses `root`). Después:

```powershell
wsl -l -v
# Ubuntu  Running/Stopped  2
# docker-desktop  ...  (eso puede seguir; ignorarlo)
wsl --set-default Ubuntu
```

Si `wsl --install` falla: Microsoft Store → **Ubuntu**, o activá “Plataforma de hipervisor de Windows” / “Subsistema de Windows para Linux” en Características de Windows.

2. Instalá [Docker Desktop](https://www.docker.com/products/docker-desktop/) si no está. Settings (engranaje):
   - **General** → *Choose how to run Docker containers* → **WSL 2** (en versiones nuevas no aparece “Use the WSL 2 based engine”; es lo mismo).
   - **Resources**: a la izquierda hay subpáginas. **Advanced** es RAM/disco y *Resource Saver* (Docker se duerme a los 5 min). La lista de Ubuntu está en **Resources → WSL integration** (no en Advanced). Activá Ubuntu.
   - En *Resource Saver*: desactivalo en la PC de la caja (si no, a los 5 min `docker` desaparece de Ubuntu).
   - Apply & Restart.

3. Abrí la app **Ubuntu** (menú Inicio → Ubuntu). **No** uses Ejecutar → `wsl` ni PowerShell para clonar. El prompt:

```text
usuario@PC:~$
```

```bash
whoami
# el usuario que creaste (no root, salvo que hayas elegido eso)
pwd
# /home/TU_USUARIO   — bien
# /mnt/c/...  o  /mnt/host/...  — mal: no es Ubuntu, volvé al paso 1
```

`sudo` es de Ubuntu. En PowerShell no existe. Si `whoami` es `root` y `pwd` empieza con `/home` o `/root`, seguí sin `sudo`.

4. Cloná en tu home (sin `sudo`):

```bash
mkdir -p ~/ventas_sistema
cd ~/ventas_sistema
git clone https://github.com/fanypan/ventas_sistema.git .
pwd
# /home/TU_USUARIO/ventas_sistema
# NO /mnt/c/Users/...
ls docker-compose.prod.yml
# si dice "No such file", no estás en el repo
```

Si ya lo habías clonado en `C:\Users\…`, no lo uses desde `/mnt/c`. Volvé a clonar como arriba. Desde el Explorador: `\\wsl$\Ubuntu\home\TU_USUARIO\ventas_sistema`.

5. Copiá el entorno **desde esa misma carpeta**:

```bash
cp .env.example .env
```

6. En `.env` **no alcanza** con los dominios. `APP_ENV` y `APP_DEBUG` los pisa el compose (`production` / `false`): no hace falta tocarlos. Sí tenés que completar esto (el `.env.example` trae claves débiles o vacías):

```env
APP_NAME="AranduTech Ventas"
APP_URL=http://arandutech.com.py
APP_KEY=base64:...
APP_PUBLISH_PORT=80
SESSION_SECURE_COOKIE=false

DB_PASSWORD=una-clave-db
REDIS_PASSWORD=una-clave-redis
MINIO_ROOT_USER=minio
MINIO_ROOT_PASSWORD=una-clave-minio
AWS_URL=http://arandutech.com.py/media/ventas-public
AWS_PUBLIC_ENDPOINT=http://arandutech.com.py/media

CENTRAL_DOMAINS=arandutech.com.py,www.arandutech.com.py,admin.arandutech.com.py
TENANT_BASE_DOMAIN=arandutech.com.py
PLATFORM_PATH=plataforma
PLATFORM_DOMAIN=admin.arandutech.com.py
PLATFORM_ADMIN_PASSWORD=una-clave-staff

RUN_MIGRATIONS=true
RUN_SEED=true
BACKUP_SCHEDULE=17:00
MAIL_MAILER=log
```

`APP_KEY` se genera **antes** del `up` (comando en [configurar `.env`](#configurar-env)). `DB_PASSWORD`, `REDIS_PASSWORD` y `MINIO_ROOT_PASSWORD` no pueden quedar `ventas` / vacíos: el compose no arranca. `PLATFORM_ADMIN_PASSWORD` es la clave del staff al sembrar. `cliente.arandutech.com.py` **no** va en `CENTRAL_DOMAINS`.

El resto (`DB_HOST=postgres`, Redis, MinIO interno) ya viene bien en `.env.example` o lo fuerza el compose. SMTP no hace falta en LAN si `MAIL_MAILER=log` (el enlace de alta queda en `docker compose logs`).

Sin HTTPS, `SESSION_SECURE_COOKIE=false` es obligatorio: si queda `true`, el login no guarda la cookie.

7. Archivo **hosts de Windows** (el navegador de Chrome/Edge lo lee; no el de Ubuntu). Abrí con Bloc de notas **como administrador**:

`C:\Windows\System32\drivers\etc\hosts`

```
127.0.0.1  arandutech.com.py www.arandutech.com.py admin.arandutech.com.py cliente.arandutech.com.py
```

`hosts` no acepta `*.arandutech.com.py`: cada nombre va en una línea. Otras PCs de la LAN: la IP de esta caja (`192.168.x.x`), no `127.0.0.1`. Celulares no leen este archivo (hace falta DNS en el router o un registro real).

8. Levantá **producción** (sigue en `~/ventas_sistema` en Ubuntu):

```bash
cd ~/ventas_sistema
docker compose -f docker-compose.prod.yml up -d --build
```

No uses `docker compose up` a secas: eso es el compose de **desarrollo** (puerto 8090).

Si aparece *The command 'docker' could not be found in this WSL 2 distro*:

1. Docker Desktop tiene que estar **abierto** (icono de la ballena en la bandeja).
2. Settings → **General** → *Choose how to run Docker containers* → **WSL 2** (es el reemplazo de “Use the WSL 2 based engine”).
3. Settings → **Resources**: no uses la página **Advanced** (ahí está *Resource Saver*, 5 min). En el menú de la izquierda, **WSL integration**. Activá:
   - Enable integration with my default WSL distro
   - Ubuntu (additional distros)
4. Si no ves Ubuntu en esa lista, en PowerShell: `wsl --set-default Ubuntu`, Apply & Restart, y volvé a mirar WSL integration.
5. **Resource Saver**: desactivalo. Si queda en 5 min, Docker se duerme y `docker` deja de existir en Ubuntu.
6. Apply & Restart. Cerrá Ubuntu y abrilo de nuevo.
7. En Ubuntu: `docker version`.

Si en **PowerShell** `docker version` funciona y en Ubuntu no, podés levantar igual desde PowerShell (con Docker Desktop abierto):

```powershell
wsl -d Ubuntu -e bash -lc "cd ~/ventas_sistema && docker compose -f docker-compose.prod.yml up -d --build"
```

No instales `docker.io` con `apt` dentro de Ubuntu.

**No** instales Laragon, XAMPP ni Composer. **No** corras `php artisan` ni `sudo` en PowerShell.

Después del alta (plan **Instalación propia**, slug `cliente`):

| Superficie | URL en el navegador de Windows |
| --- | --- |
| Landing | `http://arandutech.com.py` |
| Staff | `http://admin.arandutech.com.py/plataforma/login` |
| POS | `http://cliente.arandutech.com.py` |

Siguiente: [primer arranque](#primer-arranque) (confirmar `queue` y `scheduler` `Up`) y [crear el comercio](#b-un-solo-comercio--instalación-propia-on-prem).

### Configurar `.env`

Un `.env` **de producción**: no copies el de un notebook con `APP_DEBUG=true` y claves `ventas/ventas`.

Generá `APP_KEY` **antes** de levantar. El entrypoint de producción **no arranca** si la key está vacía. En el host no hace falta PHP:

```bash
docker run --rm php:8.3-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)), PHP_EOL;"
```

Pegá el resultado en `APP_KEY`. Variables importantes:

```env
APP_NAME="AranduTech Ventas"
APP_URL=https://tudominio.com
APP_KEY=base64:...
APP_PUBLISH_PORT=80
PLATFORM_ADMIN_PASSWORD=una-clave-staff

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
MAIL_FROM_NAME="${APP_NAME}"
```

`docker-compose.prod.yml` **fuerza** `APP_ENV=production`, `APP_DEBUG=false`, cache/sesión/cola en Redis, `TRUSTED_PROXIES=private` y `DB_HOST=postgres`. `SESSION_SECURE_COOKIE` default `true` (internet); en LAN sin HTTPS poné `false` (si no, el login no guarda cookie). No hay fallback `ventas` para `DB_PASSWORD`; Redis exige `REDIS_PASSWORD` (`requirepass`); MinIO exige `MINIO_ROOT_PASSWORD`. Nginx publica las fotos en `/media/` (por eso `AWS_URL` apunta a `https://tudominio.com/media/ventas-public`). En producción **no** se publican los puertos 9000/9001 de MinIO.

**Importante:** `POSTGRES_DB` / `POSTGRES_USER` / `POSTGRES_PASSWORD` solo se aplican la **primera** vez que se crea el volumen de Postgres. En un servidor nuevo, poné las claves definitivas **antes** del primer `up`.

`PLATFORM_PATH=plataforma` es solo para local. En internet usá un prefijo opaco: el staff entra por `https://admin.tudominio.com/{PLATFORM_PATH}/login` (bookmark interno; no hay link en la landing).

Instalación propia en LAN (sin DNS público): en **Windows** usá un dominio en el archivo `hosts` ([Windows — producción](#windows--producción)). Alternativa sin hosts: `TENANT_BASE_DOMAIN=localhost` y `CENTRAL_DOMAINS=localhost,127.0.0.1` (el POS queda en `http://{slug}.localhost`; en Windows `*.localhost` no siempre resuelve).

Este compose sirve **HTTP en el puerto 80**. En internet poné un reverse proxy con certificado (Caddy, Nginx, Traefik o el panel del VPS) delante. Laravel confía solo en redes privadas (`TRUSTED_PROXIES=private`: Docker/LAN). No uses `*` en internet.

### Primer arranque

Desde el clone (`/opt/ventas_sistema` en Linux, `~/ventas_sistema` en Ubuntu/WSL):

```bash
docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml ps
docker compose -f docker-compose.prod.yml logs -f php nginx queue scheduler
```

Si `php` está `Exit` / `Restarting` y el resto dice `dependency php failed to start`, mirá **solo** PHP:

```bash
docker compose -f docker-compose.prod.yml logs php
```

Causas habituales: `APP_KEY` vacío, `PLATFORM_ADMIN_PASSWORD` vacío con `RUN_SEED=true`, o `chown` sobre la carpeta `backups` en Windows. El mensaje concreto está al final de ese log.

Si en los logs aparece `Please provide a valid cache path`, el volumen `storage` arrancó vacío. En Ubuntu:

```bash
docker compose -f docker-compose.prod.yml exec php mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs
docker compose -f docker-compose.prod.yml exec php php artisan config:clear
docker compose -f docker-compose.prod.yml restart php queue scheduler
```

Cuando `php` esté healthy, Nginx responde en `http://SERVIDOR/` (puerto `APP_PUBLISH_PORT`, por defecto 80). Confirmá que `queue` y `scheduler` estén `Up`:

- `queue` — alta de cliente y jobs de tenancy
- `scheduler` — `subscriptions:tick` (07:00) y `tenants:backup` (`BACKUP_SCHEDULE`: hora de atención del comercio; default `02:30` solo si el servidor no se apaga)

Si `RUN_SEED=true` en ese primer arranque, definí `PLATFORM_ADMIN_PASSWORD` (en production el seeder no arranca sin eso). En el próximo reciclo poné `RUN_SEED=false`.

Si no sembraste, creá el usuario staff a mano o corré el seeder una sola vez:

```bash
docker compose -f docker-compose.prod.yml exec php php artisan db:seed --force
```

Firewall: abrí **80** y **443**. No abras 5432, 6379 ni 9000/9001.

El usuario de Postgres tiene que poder `CREATE DATABASE` (en este compose ya es superuser). Si el volumen era viejo:

```sql
ALTER ROLE ventas CREATEDB;
```

### Cómo entrar

| Superficie             | URL (producción)                                             | Usuario                         | Contraseña                   |
| ---------------------- | ------------------------------------------------------------ | ------------------------------- | ---------------------------- |
| Landing                | `https://tudominio.com`                                      | —                               | —                            |
| **Staff** (plataforma) | `https://admin.tudominio.com/{PLATFORM_PATH}/login`          | el mail del seed (`plataforma@…`) | `PLATFORM_ADMIN_PASSWORD` |
| POS de un comercio     | `https://{slug}.tudominio.com`                               | el mail del alta                | enlace de 48 h por mail      |

**PC Windows en LAN** (archivo `hosts`, sin TLS): landing `http://arandutech.com.py`, staff `http://admin.arandutech.com.py/plataforma/login`, POS `http://cliente.arandutech.com.py`. Detalle: [Windows — producción](#windows--producción).

Los usuarios `superadmin@` / `admin@` / `operator@` **ya no se siembran** en la base central. Cada comercio nace con un `admin` (el mail del alta) y el rol `operator` listo para asignar.

Siguiente paso: [crear el primer comercio](#crear-comercios-saas-o-un-solo-comercio).

El POS del comercio se puede **instalar como PWA** (Chrome/Android; en iOS, Compartir → Agregar a pantalla de inicio). La landing y el panel staff no son PWA.

### HTTPS y DNS

Ejemplo mínimo con Caddy en el host (TLS wildcard; el compose sigue en el puerto 80):

```caddy
tudominio.com, www.tudominio.com, admin.tudominio.com, *.tudominio.com {
    reverse_proxy 127.0.0.1:80
}
```

`APP_URL` tiene que ser exactamente la URL pública (`https://tudominio.com`).

| Host                                  | Qué es           |
| ------------------------------------- | ---------------- |
| `tudominio.com`                       | landing y planes |
| `admin.tudominio.com/{PLATFORM_PATH}` | panel staff      |
| `{slug}.tudominio.com`                | POS del comercio |

Opcional: restringir el panel staff por IP (`docker/nginx/platform-staff.conf.example`).

Si el proxy usa otro puerto publicado (`APP_PUBLISH_PORT=8080`), el `reverse_proxy` apunta a `127.0.0.1:8080`.

### Actualizar la app

Los datos viven en volúmenes Docker y **no se borran** al rebuild: Postgres, Redis, `storage` (logs, cache), la carpeta `./backups` del host (dumps SQL) y **MinIO** (fotos de producto, gestor de archivos, comprobantes de pago).

```bash
cd /opt/ventas_sistema
git pull
docker compose -f docker-compose.prod.yml up -d --build
```

El entrypoint vuelve a correr migraciones (`RUN_MIGRATIONS=true`) y regenera `config` / `route` / `view` cache.

### Operación diaria

```bash
docker compose -f docker-compose.prod.yml ps

docker compose -f docker-compose.prod.yml logs -f php nginx queue scheduler postgres

docker compose -f docker-compose.prod.yml exec php php artisan about
docker compose -f docker-compose.prod.yml exec php php artisan permission:cache-reset

# central.sql.gz + un dump por tenant → ./backups/{fecha}/ (o BACKUP_HOST_PATH)
mkdir -p backups
docker compose -f docker-compose.prod.yml exec php php artisan tenants:backup
```

Un `pg_dump` solo de `ventas_central` **no** alcanza: cada comercio es otra base (`tenant_demo`, etc.). Restaurar: `gunzip -c archivo.sql.gz | psql ...`.

On-prem en una PC: poné `BACKUP_SCHEDULE` al horario de atención (si apagan la caja al cerrar, `02:30` no corre). Sincronizá `./backups` con Google Drive (o poné `BACKUP_HOST_PATH` a la carpeta de Drive y recreá `php` + `scheduler`). El scheduler tiene que estar `Up`. Detalle: [docs/SAAS.md](docs/SAAS.md#backups).

Bases huérfanas (sin cliente en plataforma):

```bash
docker compose -f docker-compose.prod.yml exec php php artisan tenants:cleanup-orphans --dry-run
docker compose -f docker-compose.prod.yml exec php php artisan tenants:cleanup-orphans
```

Backup de archivos: carpeta `./backups` (SQL, montada desde el host) **y** volumen `ventas_sistema_prod_minio_data` (fotos y uploads). Un dump SQL no alcanza.

Para bajar el stack **sin** borrar datos:

```bash
docker compose -f docker-compose.prod.yml down
```

`down -v` **borra** Postgres, Redis, storage y MinIO. No lo uses en producción salvo que quieras resetear todo.

### Checklist

- [ ] `APP_KEY` definido y **nunca** regenerado después de tener datos
- [ ] `APP_DEBUG=false` (el compose ya lo fuerza)
- [ ] Contraseña de DB distinta a `ventas`
- [ ] `REDIS_PASSWORD` y `MINIO_ROOT_PASSWORD` distintos a los de local
- [ ] `AWS_URL` / `AWS_PUBLIC_ENDPOINT` con `https://tudominio.com/media/…`
- [ ] `CENTRAL_DOMAINS`, `TENANT_BASE_DOMAIN`, `PLATFORM_PATH` (opaco) y `PLATFORM_DOMAIN`
- [ ] DNS wildcard `*.tudominio.com` + TLS wildcard (SaaS)
- [ ] `queue` y `scheduler` en `Up`
- [ ] SMTP real (`MAIL_MAILER=smtp`) si vas a dar de alta clientes
- [ ] `RUN_SEED=false` después del primer deploy
- [ ] `PLATFORM_ADMIN_PASSWORD` si sembrás staff en prod (no dejes `plataforma`)
- [ ] `APP_URL` con `https://` (en internet)
- [ ] Puerto 5432/6379/9000/9001 no publicados
- [ ] `BACKUP_SCHEDULE` en horario de atención si la PC se apaga al cerrar (VPS 24/7: `02:30` alcanza)
- [ ] Carpeta `./backups` (o `BACKUP_HOST_PATH`) sincronizada a otro disco/Drive; copias del volumen MinIO aparte

---

## Crear comercios: SaaS o un solo comercio

El stack es **el mismo** (`docker-compose.prod.yml`: central + cola + una PostgreSQL por comercio). Lo que cambia es cuántos tenants creás, el plan y el DNS. Detalle operativo: [docs/SAAS.md](docs/SAAS.md).

### A) SaaS — varios comercios en un servidor

Para AranduTech (o un partner) que vende suscripciones mensuales/anuales.

1. Levantá producción con DNS **wildcard** `*.tudominio.com` y las vars de dominio:

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

El comercio hostea en su VPS o en la LAN. Mismo [instalación de producción](#instalación-producción). Un tenant, plan interno **Instalación propia** (`onprem`): no sale en la landing, sin tope de usuarios/cajas, **sin vencimiento**, sin FE en el cupo del plan. La licencia se cobra afuera del panel.

1. En `.env`, apuntá al host del comercio (no hace falta wildcard si hay un solo POS):

```env
APP_URL=https://pos.minegocio.com
CENTRAL_DOMAINS=pos.minegocio.com,admin.minegocio.com
TENANT_BASE_DOMAIN=minegocio.com
PLATFORM_PATH=plataforma
PLATFORM_DOMAIN=admin.minegocio.com
```

En la **PC Windows** del comercio, sin DNS público: dominio en `C:\Windows\System32\drivers\etc\hosts` y `.env` como en [Windows — producción](#windows--producción) (`cliente.arandutech.com.py`). Alternativa: `TENANT_BASE_DOMAIN=localhost` y `CENTRAL_DOMAINS=localhost,127.0.0.1` (más la IP de la PC si entran por IP).

2. Con el seed (`RUN_SEED=true` la primera vez) queda el plan **Instalación propia**.
3. Staff → **Nuevo cliente** → plan **Instalación propia**. El período se fuerza a **Sin vencimiento**. No registres pago mensual: `subscriptions:tick` no pausa este plan.
4. El POS queda en `{slug}.{TENANT_BASE_DOMAIN}` (ej. slug `pos` → `pos.minegocio.com`). Si el comercio quiere entrar por el apex (`minegocio.com`), agregá ese host en la tabla `domains` del tenant (además del subdominio que crea el alta).
5. Cambiá la clave del staff sembrado y poné `RUN_SEED=false`.
6. Backups SQL: `BACKUP_SCHEDULE` en `.env` con una hora **en la que el comercio esté abierto** (la PC tiene que estar prendida; p.ej. `17:00` o `13:00,19:30`). El default `02:30` solo sirve en un VPS que no se apaga. `mkdir -p backups` y sincronizá esa carpeta con Google Drive (o `BACKUP_HOST_PATH`). Hace falta el `scheduler`. Tras cambiar el horario, recreá `scheduler`. [docs/SAAS.md](docs/SAAS.md#backups).

No uses un plan “gratis” público para esto: canibaliza Starter/Negocio y aparece en la landing.

|                       | SaaS                          | Instalación propia                                |
| --------------------- | ----------------------------- | ------------------------------------------------- |
| Plan                  | público (Starter, Negocio, …) | `onprem` (interno)                                |
| Período               | mensual / anual               | sin vencimiento                                   |
| Pago en panel         | sí (renueva)                  | no (licencia afuera)                              |
| DNS                   | wildcard `*.dominio`          | un host o LAN alcanza                             |
| Cantidad de comercios | muchos                        | uno (puede haber más, pero el caso típico es uno) |

---

## Desarrollo local

Para **programar** el POS en una notebook. No es el camino de un comercio ni de un VPS: esos usan [producción](#instalación-producción).

`docker-compose.yml` monta el código y corre `php artisan serve` en el puerto **8090**. El entrypoint de desarrollo hace `composer install`, `key:generate`, `storage:link`, migrate y seed. **No** corrás esos comandos en el host.

Los perfiles `mail` y `obs` son extras. Un `up` a secas ya es app + cola + Postgres + Redis + MinIO.

### Linux

1. Cloná y **entrá** a la carpeta:

```bash
git clone https://github.com/fanypan/ventas_sistema.git
cd ventas_sistema
pwd
ls docker-compose.yml
```

2. Copiá el entorno. `.env.example` ya trae `DB_HOST=postgres`, `QUEUE_CONNECTION=redis` y `WWWUSER=1000`. Ajustá el uid si el tuyo no es 1000 (`id -u` / `id -g`):

```bash
cp .env.example .env
```

El entrypoint hace `chown` de `storage` y `bootstrap/cache` al uid de `WWWUSER`. Si un arranque viejo dejó `vendor` de root:

```bash
sudo chown -R "$(id -u):$(id -g)" vendor storage bootstrap/cache
```

3. Desde **esta misma carpeta**:

```bash
docker compose up -d --build
```

4. Esperá a que `app` y `queue` estén `Up` (`docker compose ps`). App: **[http://localhost:8090](http://localhost:8090)**. MinIO: **[http://localhost:9001](http://localhost:9001)** (`minioadmin` / `minioadmin`). Postgres en el host: `127.0.0.1:5433` (`ventas` / `ventas`, base `ventas_central`).

### Windows — desarrollo

Esto **no** instala el POS de un comercio. Para la PC de la caja usá [Windows — producción](#windows--producción).

Mismos Docker Desktop + WSL 2, pero el clone va en el home de Ubuntu (no hace falta `/opt`) y el compose es el de **desarrollo** (`docker-compose.yml`, puerto 8090).

1. Terminal **de Ubuntu** (no PowerShell sobre `C:\`).
2. Cloná adentro de WSL:

```bash
mkdir -p ~/proyectos
cd ~/proyectos
git clone https://github.com/fanypan/ventas_sistema.git
cd ventas_sistema
pwd
# /home/TU_USUARIO/proyectos/ventas_sistema — NO /mnt/c/Users/...
ls docker-compose.yml
```

3. Entorno y stack:

```bash
cp .env.example .env
docker compose up -d --build
docker compose ps
```

4. Navegador de Windows: **[http://localhost:8090](http://localhost:8090)**.

No instales Laragon ni Composer. No uses `docker-compose.prod.yml` para programar (el código no se monta; cada cambio exigiría rebuild).

### Qué servicios levantar

```bash
# POS: app, cola, Postgres, Redis, MinIO
docker compose up -d --build

# + Mailpit (bandeja en http://localhost:8025). En .env: MAIL_MAILER=smtp
docker compose --profile mail up -d

# + Dozzle y Uptime Kuma (solo desarrollo)
docker compose --profile obs up -d
```

Se pueden combinar: `docker compose --profile mail --profile obs up -d`.

| Servicio                      | Default `up` | Perfil | Para qué                                |
| ----------------------------- | ------------ | ------ | --------------------------------------- |
| `app` + `queue` + `scheduler` | sí           | —      | POS, cola, dumps y `subscriptions:tick` |
| `postgres` + `redis`          | sí           | —      | bases y colas                           |
| `minio` + `minio-init`        | sí           | —      | fotos y archivos                        |
| `mailhog` (Mailpit)           | no           | `mail` | mails de prueba                         |
| `dozzle` + `kuma`             | no           | `obs`  | logs y uptime de desarrollo             |

Sin Mailpit, `MAIL_MAILER=log` (así viene `.env.example`): el enlace del alta queda en el log. Para verlo en bandeja: `MAIL_MAILER=smtp` y `--profile mail`.

### Cómo entrar (local)

| Superficie             | URL                                                                          | Usuario                     | Contraseña   |
| ---------------------- | ---------------------------------------------------------------------------- | --------------------------- | ------------ |
| Landing                | [http://localhost:8090](http://localhost:8090)                               | —                           | —            |
| **Staff** (plataforma) | [http://localhost:8090/plataforma/login](http://localhost:8090/plataforma/login) | `plataforma@arandutech.com` | `plataforma` |
| POS de un comercio     | http://{slug}.localhost:8090                                                 | el mail del alta            | por mail     |

`*.localhost` resuelve solo. El tenant de prueba aparece después de dar de alta un cliente en el panel staff.

reCAPTCHA es opcional en local. Si lo usás: [consola reCAPTCHA v2](https://www.google.com/recaptcha/admin) y las claves `RECAPTCHA_SITE_KEY` / `RECAPTCHA_SECRET_KEY` en `.env`.

### Observabilidad y calidad

Apagado por default. En la PC de un comercio no uses `--profile obs` ni `--profile mail`.

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

## Alternativa: sin Docker

**Si ya usás Docker, no leas esto.** No instales Laragon, XAMPP ni Composer.

Solo si no podés usar Compose: PHP 8.3, Composer, PostgreSQL 16 y Redis en el host, y `composer` / `artisan` a mano. Redis es requerido para colas (`QUEUE_CONNECTION=redis`): el alta de un comercio corre en background. Esto no reemplaza [producción](#instalación-producción).

### Linux (PHP del host)

Desde la carpeta del repo (`cd ventas_sistema` después del clone):

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

App: **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

### Windows (Laragon/XAMPP, sin Docker)

Opciones habituales: [Laragon](https://laragon.org/) (PHP + PostgreSQL + Composer) o XAMPP + PostgreSQL + [Composer](https://getcomposer.org/Composer-Setup.exe). Instalá Redis (o usá Docker solo para Redis).

En PowerShell, **adentro de la carpeta del repo** (`cd ruta\a\ventas_sistema`), con PHP y Composer en el `PATH`:

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

## Módulos de negocio

Los CRUDs del POS viven en `Modules/` y corren **en la DB del comercio**, no en `ventas_central`. Hoy: Sales, Products, Purchases, Customers, Suppliers, Credits, Financials, StockAdjustments.

En desarrollo:

```bash
docker compose exec app php artisan module:build
# nombre en plural: warranties, categories, etc.
```

El stub ya sale tenant-safe (`TenantMiddleware`, sin `loadMigrationsFrom`). Después del generate:

1. `module.json`: `menus` + `permissions` en **singular** (`warranty`, no `warranties`).
2. Recargar permisos en el panel del comercio, o incluir el recurso en `BusinessPermissionSeeder`.
3. `admin` ve el CRUD; `operator` solo lo que va a caja. No crear `superadmin` de tenant.

Detalle: [`.cursor/skills/modulo-negocio/SKILL.md`](.cursor/skills/modulo-negocio/SKILL.md). No rearmar menús del POS.

```bash
docker compose exec app php artisan module:enable {Nombre}
docker compose exec app php artisan module:disable {Nombre}
```
