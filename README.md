 ## Features
- SaaS multi-tenant (una DB por cliente, subdominio, panel de onboarding): ver [docs/SAAS.md](docs/SAAS.md)
- Login Page
    [![login.png](https://i.postimg.cc/vmCY7mwC/login.png)](https://postimg.cc/67Lxtt3h)
- User Management
    [![user-management.png](https://i.postimg.cc/sDfrhWZv/user-management.png)](https://postimg.cc/LhWwdnDp)
- Role Management
    [![role-management.png](https://i.postimg.cc/tCvLnMjM/role-management.png)](https://postimg.cc/w7JWSF3X)
- Permission Management
    [![permission-management.png](https://i.postimg.cc/gJK7zMs4/permission-management.png)](https://postimg.cc/YGhR8zJm)
- Dynamic website settings
    [![website-setting.png](https://i.postimg.cc/MTzsF7wB/website-setting.png)](https://postimg.cc/zLPSLRVD)
- View installed module
    [![module-view.png](https://i.postimg.cc/JzJP764J/module-view.png)](https://postimg.cc/ZWbrVLNK)
- File manager
    [![file-manager.png](https://i.postimg.cc/mDdSpK8x/file-manager.png)](https://postimg.cc/FdLc7WMG)
- File picker
    [![file-picker.png](https://i.postimg.cc/Fz7VMGBY/file-picker.png)](https://postimg.cc/n9fm7KZx)

## Packages
- [Admin LTE 3 Template](https://github.com/ColorlibHQ/AdminLTE)
- Laravel UI (Bootstrap)
- Laravel Auth
- [Google recaptcha](https://laravel-recaptcha-docs.biscolab.com/docs/intro)
- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar)
- [Spatie](https://spatie.be/docs/laravel-permission/v5/introduction)
- [Sweet Alert](https://github.com/realrashid/sweet-alert)
- [File Manager](https://github.com/alexusmai/laravel-file-manager)
- [Laravel Module](https://nwidart.com/laravel-modules/v6/introduction)
- [Laravel Module Generator](https://github.com/dcblogdev/laravel-module-generator)

## Requisitos

**Con Docker (recomendado):** [Docker Engine](https://docs.docker.com/engine/install/) + plugin Compose v2, o [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows / Linux).

**Sin Docker:** PHP 8.2, Composer, MySQL 8, extensiones `pdo_mysql`, `gd`, `zip`, `bcmath`, `intl`, `exif`. Redis es **requerido** para colas del SaaS (`QUEUE_CONNECTION=redis`).

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

En `.env` dejá `DB_HOST=mysql` y el uid del host:

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
   MySQL desde el host: `127.0.0.1:3307` (usuario/clave `ventas` / `ventas`, base `admin_lte3`).

La primera vez el entrypoint corre `composer install`, migraciones y seed.

Si ya existía la base y no se sembraron permisos de negocio:

```bash
docker compose exec app php artisan db:seed --class=BusinessPermissionSeeder --force
```

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

Dejá `DB_HOST=mysql` en `.env` (es el nombre del servicio, no `127.0.0.1`).  
`WWWUSER` / `WWWGROUP` en `1000` está bien con Docker Desktop + WSL 2.

Si `docker compose` no existe, usá `docker-compose` (guión) o actualizá Docker Desktop.

---

### Linux — sin Docker

```bash
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-mysql php8.2-gd php8.2-zip php8.2-bcmath php8.2-intl php8.2-xml php8.2-mbstring php8.2-curl unzip
# Composer: https://getcomposer.org/download/
```

Creá la base `admin_lte3` en MySQL. Luego:

```bash
cp .env.example .env
```

En `.env` (valores típicos en el host):

```env
APP_URL=http://127.0.0.1:8000
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=admin_lte3
DB_USERNAME=root
DB_PASSWORD=
REDIS_HOST=127.0.0.1
MAIL_MAILER=log
```

```bash
composer install
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
php artisan serve
```

App: **http://127.0.0.1:8000**

---

### Windows — sin Docker

Opciones habituales: [Laragon](https://laragon.org/) (PHP + MySQL + Composer) o XAMPP + [Composer](https://getcomposer.org/Composer-Setup.exe).

En PowerShell, con PHP y Composer en el `PATH`:

```powershell
copy .env.example .env
```

Editá `.env` igual que en Linux sin Docker (`DB_HOST=127.0.0.1`, `DB_PORT=3306`, usuario/clave de tu MySQL local).

```powershell
composer install
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
php artisan serve
```

Si `php` no se reconoce, usá la ruta de Laragon/XAMPP, por ejemplo:

```powershell
C:\laragon\bin\php\php-8.2.0-Win32-vs16-x64\php.exe artisan serve
```

---

### Usuarios de prueba (después del seed)

| Rol         | Email                         | Contraseña   |
|-------------|-------------------------------|--------------|
| Superadmin  | superadmin@superadmin.com     | superadmin   |
| Admin       | admin@admin.com               | admin        |
| Operator    | operator@operator.com         | operator     |

reCAPTCHA es opcional en local. Si lo usás: [consola reCAPTCHA v2](https://www.google.com/recaptcha/admin) y las claves `RECAPTCHA_SITE_KEY` / `RECAPTCHA_SECRET_KEY` en `.env`.

---

## Puesta en producción

El archivo `docker-compose.yml` es **solo desarrollo** (`php artisan serve`). En producción usá **`docker-compose.prod.yml`**: Nginx + PHP-FPM 8.2 + MySQL 8 + Redis. El código va **dentro de la imagen** (no se monta el disco). MySQL y Redis **no** se publican al host.

Proyecto Compose aparte (`ventas_sistema_prod`): no pisa volúmenes ni red del stack local.

### Qué tenés que tener

- Un VPS **Linux** (Ubuntu 22.04/24.04) con Docker Engine + plugin Compose v2. Es el camino recomendado.
- En **Windows** se puede con Docker Desktop, pero para un local de ventas conviene Linux (o WSL 2 en el servidor no aplica: el servidor debería ser Linux).
- Dominio apuntando a la IP del servidor (si vas a usar HTTPS).
- Un `.env` **de producción** en el servidor: no copies el `.env` de tu notebook con `APP_DEBUG=true` y claves `ventas/ventas`.

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

En una máquina con PHP/Composer (o en el servidor si ya tenés PHP):

```bash
php artisan key:generate --show
```

Si todavía no hay `vendor`, desde Docker:

```bash
docker run --rm php:8.2-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)), PHP_EOL;"
```

Pegá el resultado en `APP_KEY`. Variables importantes:

```env
APP_NAME="Sistema de Ventas"
APP_URL=https://ventas.tudominio.com
APP_KEY=base64:...          # obligatorio
APP_PUBLISH_PORT=80         # puerto publicado de Nginx (443 lo maneja el proxy, ver más abajo)

DB_HOST=mysql               # nombre del servicio, no 127.0.0.1
DB_DATABASE=admin_lte3
DB_USERNAME=ventas
DB_PASSWORD=una-clave-larga
MYSQL_ROOT_PASSWORD=otra-clave-larga

RUN_MIGRATIONS=true
RUN_SEED=true               # solo el primer arranque, después false

MAIL_MAILER=log             # o smtp real (MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD)
```

`docker-compose.prod.yml` **fuerza** `APP_ENV=production`, `APP_DEBUG=false`, cache/sesión en Redis y `DB_HOST=mysql`. Las claves de MySQL se leen de este `.env`.

**Importante:** las variables `MYSQL_*` solo se aplican la **primera** vez que se crea el volumen `mysql_data`. Si levantaste con `ventas/ventas` y después cambiás la clave en `.env`, MySQL sigue con la clave vieja. En un servidor nuevo, poné las claves definitivas **antes** del primer `up`.

### 3. Primer arranque

**Linux:**

```bash
docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml ps
docker compose -f docker-compose.prod.yml logs -f php nginx
```

**Windows (PowerShell, Docker Desktop):**

```powershell
copy .env.example .env
# editá .env (APP_KEY, APP_URL, DB_PASSWORD, MYSQL_ROOT_PASSWORD)
docker compose -f docker-compose.prod.yml up -d --build
```

Cuando `php` esté healthy, Nginx responde en `http://SERVIDOR/` (puerto `APP_PUBLISH_PORT`, por defecto 80).

Si `RUN_SEED=true` en ese primer arranque, existen los usuarios de la tabla de arriba. **Cambiá esas contraseñas** (o desactivá esas cuentas) y en el próximo reciclo poné `RUN_SEED=false` para no volver a sembrar.

Si no sembraste (`RUN_SEED=false`), no hay usuarios: entrá al contenedor y creá uno, o corré el seeder una sola vez:

```bash
docker compose -f docker-compose.prod.yml exec php php artisan db:seed --force
```

Firewall: abrí **80** y **443**. No abras 3306 ni 6379.

### 4. HTTPS (dominio)

Este compose sirve **HTTP en el puerto 80**. En internet poné un reverse proxy con certificado (Caddy, Nginx, Traefik o el panel del VPS) delante, o Cloudflare.

Ejemplo mínimo con Caddy en el host, proxy a Nginx del compose en el puerto 80:

```caddy
ventas.tudominio.com {
    reverse_proxy 127.0.0.1:80
}
```

`APP_URL` tiene que ser exactamente la URL pública (`https://ventas.tudominio.com`). Laravel ya confía en el proxy (`TrustProxies`).

Si el proxy usa otro puerto publicado, por ejemplo `APP_PUBLISH_PORT=8080`, el `reverse_proxy` apunta a `127.0.0.1:8080`.

### 5. Actualizar la app

Los datos (MySQL, Redis, `storage` con logos y uploads) viven en volúmenes Docker y **no se borran** al rebuild.

```bash
cd ventas_sistema
git pull
docker compose -f docker-compose.prod.yml up -d --build
```

El entrypoint vuelve a correr migraciones (`RUN_MIGRATIONS=true`) y regenera `config` / `route` / `view` cache.

### 6. Operación diaria

```bash
# estado
docker compose -f docker-compose.prod.yml ps

# logs (PHP escribe a stderr)
docker compose -f docker-compose.prod.yml logs -f php nginx mysql

# Artisan
docker compose -f docker-compose.prod.yml exec php php artisan about
docker compose -f docker-compose.prod.yml exec php php artisan permission:cache-reset

# backup de la base (archivo en el directorio actual del host)
docker compose -f docker-compose.prod.yml exec -T mysql \
  sh -c 'mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' > backup-$(date +%F).sql
```

Backup de archivos subidos: el volumen `ventas_sistema_prod_storage_data` (nombre aproximado; listalo con `docker volume ls`).

Para bajar el stack **sin** borrar datos:

```bash
docker compose -f docker-compose.prod.yml down
```

`down -v` **borra** MySQL, Redis y storage. No lo uses en producción salvo que quieras resetear todo.

### 7. Checklist

- [ ] `APP_KEY` definido y **nunca** regenerado después de tener datos (invalida sesiones y cifrado)
- [ ] `APP_DEBUG=false` (el compose ya lo fuerza)
- [ ] Contraseñas de DB distintas a las de ejemplo
- [ ] `RUN_SEED=false` después del primer deploy
- [ ] Contraseñas de `superadmin` / `admin` / `operator` cambiadas
- [ ] `APP_URL` con `https://` si hay TLS
- [ ] Puerto 3306/6379 no publicados
- [ ] Copias de `mysqldump` y de `storage` en otro disco/nube


| Rol         | Email                         | Contraseña   |
|-------------|-------------------------------|--------------|
| Superadmin  | superadmin@superadmin.com     | superadmin   |
| Admin       | admin@admin.com               | admin        |
| Operator    | operator@operator.com         | operator     |

reCAPTCHA es opcional en local. Si lo usás: [consola reCAPTCHA v2](https://www.google.com/recaptcha/admin) y las claves `RECAPTCHA_SITE_KEY` / `RECAPTCHA_SECRET_KEY` en `.env`.

## Modules
### build a new module
``` bash
php artisan module:build
type the module name (plural). example : posts, categories, sliders etc.
```
### Enable module
``` bash
php artisan module:enable {module name}
```
### Disable module
``` bash
php artisan module:disable {module name}
```
### All module files will be generated in root/Modules/{Modulename}
### To automatically update permission, go to permission page and click the reload button
### Change module config
``` bash
Update the module config in root/Modules/{module name}/module.json
"menus": [
    {
        "icon": "fas fa-image",
        "name": "{ModuleName}",
        "route": "route.name",
        "permission": "read {modulename}"
    }
],
"permissions": ["{module name}"]
```
### If you need add menu in created module
``` bash
Update the module config in root/Modules/{Modulename}/module.json
"menus": [
    {
        "icon": "fas fa-image",
        "name": "{Module Name}",
        "route": "route.name",
        "permission": "read {modulename}"
    },
    {
        "icon": "fas fa-images",
        "name": "{Module Name}",
        "route": "route.name",
        "permission": "read {modulename}"
    }
],
"permissions": ["{modulename}", "{modulename}"]
```
### Then reload the permission in Permission > Reload Permission

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
