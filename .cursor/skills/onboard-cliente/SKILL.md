---
name: onboard-cliente
description: >-
  Walks through sales-assisted onboarding and manual billing for a Paraguay
  comercio. Use when adding a client, provisioning a tenant, registering a
  transferencia/efectivo payment, changing plans, grace/readonly/suspend
  states, or editing the Platform panel.
---

# Onboard de cliente

Flujo humano + panel (no hay self-serve):

1. Cierre por WhatsApp.
2. Staff en `/plataforma` → **Nuevo cliente** (nombre, RUC, slug, plan, mail admin).
3. Verificar que existan `domains.domain` = `{slug}.{TENANT_BASE_DOMAIN}` y `provisioned_at`.
4. Opcional: copiar el catálogo desde otro comercio (alta o ficha → **Copiar catálogo**). Stock en 0; no pisa códigos existentes. Permiso `tenants.catalog`.
5. El comercio recibe un mail con un enlace de 48 h para definir la contraseña (staff puede reenviar desde la ficha).
6. **Registrar pago** para arrancar/renovar el período (salvo plan **Instalación propia**: no vence, no hay cobro mensual).

## Estados

`pending` (aprovisionando) → `active` → `grace` 7d → `readonly` 3d → `suspended`.

- `readonly`: el middleware bloquea POST del POS.
- `suspended`: vista “cuenta en pausa”.
- Reactivar: registrar pago o botón Reactivar.

Código: `TenantController`, `PaymentController`, `SubscriptionService`, `EnsureTenantSubscription`. UI del panel: [DESIGN.md](../../../DESIGN.md) + `resources/views/platform/`.

Roles de plataforma (guard `platform`, tablas Spatie en la DB **central**): se asignan en **Equipo**. Por defecto **staff** da de alta, cobra y suspende; **billing** solo ve clientes y registra pagos; **admin** ve todo (baja, borrar tenant, planes, usuarios). No crear un rol `superadmin` en la plataforma: ese nombre es del POS (guard `web` en la DB del comercio).

No agregar pasarelas de cobro. Precios en Gs. enteros. Factura electrónica: el comercio pega URL + API key de `api_facturacion_electronica` en su panel; el certificado F1 vive en esa API, no acá.
