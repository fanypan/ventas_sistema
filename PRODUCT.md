# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

El mismo equipo atiende el mostrador y administra el negocio: cobra en el punto de venta y, en la misma jornada, carga stock, créditos, compras y reportes.

## Product Purpose

Sistema de ventas para operar el día del negocio: cobrar, controlar inventario, registrar compras y créditos, y cerrar caja con números confiables. El diseño funciona si una venta se cobra en segundos y, al final del día, stock, deudas y caja se entienden sin pelearse con la pantalla.

## Positioning

Un solo sistema para caja y gestión, no una caja aislada ni un back-office aparte.

## Operating Context

Se usa en computadoras del local (mostrador y escritorio), en español. La app corre en Laravel con plantilla AdminLTE, módulos de negocio (ventas, productos, compras, créditos, clientes, proveedores, finanzas, ajustes de stock) y un compose de producción que copia el código a la imagen (no monta el repo en caliente).

## Capabilities and Constraints

- Funciones actuales: login, dashboard, POS, clientes, inventario, ventas, compras, créditos, proveedores, finanzas, usuarios/roles, ajustes.
- Conservar la navegación y el esquema actual de menús y páginas. Se puede mejorar look, tipografía, densidad y pulido; no rearmar la información architecture.
- Conservar logo y nombre configurables (`app_name`, `app_logo`).
- Sistema visual en [DESIGN.md](DESIGN.md): Outfit, índigo en POS/plataforma, teal solo en landing.
- Stack vigente: Laravel, AdminLTE 3, Bootstrap 4, módulos nwidart.

## Brand Commitments

- Nombre y logo salen de la configuración del sistema, no están fijos en código.
- Voz en español rioplatense en superficies de autenticación (“Ingresá”, “Usá tu correo”).
- No hay paleta o identidad visual contractual aparte de esos assets; los tokens viven en [DESIGN.md](DESIGN.md).

## Evidence on Hand

- Logo y favicon en `storage` / settings (`storage/logo.png`).
- No hay testimonios, casos de clientes ni benchmarks reales; no inventarlos.

## Product Principles

- La caja no espera: acciones primarias grandes, un paso, texto que no se parte.
- Caja y administración se sienten el mismo producto, no dos skins.
- Densidad útil en pantallas anchas de escritorio; no dejar controles de 360px perdidos en 1800px.
- Lo familiar de un sistema de gestión se queda; el adorno que no ayuda a cobrar o controlar, se va.
