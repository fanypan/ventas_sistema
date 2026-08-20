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
4. Anotar la contraseña del flash **una vez** (también va por mail).
5. **Registrar pago** para arrancar/renovar el período.

## Estados

`pending` (aprovisionando) → `active` → `grace` 7d → `readonly` 3d → `suspended`.

- `readonly`: el middleware bloquea POST del POS.
- `suspended`: vista “cuenta en pausa”.
- Reactivar: registrar pago o botón Reactivar.

Código: `TenantController`, `PaymentController`, `SubscriptionService`, `EnsureTenantSubscription`.

No agregar pasarelas de cobro. Precios en Gs. enteros. Factura electrónica: el comercio pega URL + API key de `api_facturacion_electronica` en su panel; el certificado F1 vive en esa API, no acá.
