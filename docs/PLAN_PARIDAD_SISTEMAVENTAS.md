# Plan de paridad: Sistemaventas → Laravel

Fuente: `Sistemaventas/sistema` (PHP + mysqli). Destino: `Modules/` + `app/` (Laravel 9).

No migrar `debug_*.php`, `test_*.php`, `diag_*.php` ni copias (`- copia.php`).

---

## Oleada 1 — hecha

- Códigos de barras (CODE128 desde Productos)
- Consumo de insumos (resta stock; anular restaura)
- Editar cabecera de venta (no renglones)
- Histórico de arqueos por rango de fechas

## Oleada 2 — hecha

- Kardex cliente/proveedor (cargo, abono, saldo, PDF, recibo)
- Stock 0 (listado + Excel)
- Ajuste de stock en menú Inventario
- Dashboard con KPIs y gráficos (más vendidos / stock bajo)
- PDF de egresos (gasto vs insumo)

## Oleada 3 — hecha

- `storage/app/public/loading.gif` (seeder `app_loading_gif`)
- Permisos Spatie en módulos de negocio: menú, rutas y botones (anular venta, editar)
- Roles: `superadmin` (todo), `admin` (negocio + usuarios/settings), `operator` (POS/caja/créditos por cobrar, sin compras ni anular)
- Ajuste de stock movido al módulo `StockAdjustments`
- `docker-compose.yml` documentado como solo local (`php artisan serve`)
- Botones de alta/edición/baja en listados con `@can` (create/update/delete)
- Compose de producción: `docker-compose.prod.yml` (nginx + php-fpm + MySQL + Redis)

---
