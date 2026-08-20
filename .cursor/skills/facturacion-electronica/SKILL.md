---
name: facturacion-electronica
description: >-
  Configures and emits electronic invoices from this POS by calling the
  AranduTech facturación API (api_facturacion_electronica). Use when adding
  FE settings in the tenant admin panel, emitting after a sale, storing CDC
  status, or anything named SIFEN/SET/DNIT/KuDE in this repo.
---

# Facturación electrónica (cliente HTTP)

Este proyecto **no se conecta a SIFEN**. Emite contra la pasarela:

`POST {FACTURACION_API_URL}/api/v1/documents`  
`Authorization: Bearer sk_test_…` o `sk_live_…`

Contrato: `api_facturacion_electronica/docs/contracts/laravel-public-api.md`.

## Panel del comercio

Settings de admin (no el panel Platform): URL del servicio + API key. Opcional establecimiento/punto para el timbrado. Las keys las emite el panel de la API, no este POS.

## Al cerrar la venta

1. Persistir la venta y hacer `DB::commit`.
2. Armar `cliente` + `items` inline (nombres TIPS). **Prohibido** mandar `numero`.
3. POST a `/api/v1/documents`. Esperar **202** + id; no bloquear la caja por CDC.
4. Guardar rastro local (`sifen_documents` o equivalente). Si la API falla, la venta sigue.

## Fuera de este repo

XML, firma, SET, certificados F1, KuDE, eventos (cancelar/inutilizar): `api_facturacion_electronica`. El `sifen-adapter` Deno no es un endpoint de este POS.
